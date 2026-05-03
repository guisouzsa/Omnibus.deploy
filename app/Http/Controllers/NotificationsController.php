<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Route;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    /**
     * Listar notificações para motoristas (próprias) ou secretárias (todos motoristas)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = max(1, min((int) $request->query('per_page', 15), 100));

        // Se é motorista, retorna apenas suas notificações
        if ($user->is_driver) {
            $notifications = $user->notifications()
                ->with('route', 'driver')
                ->latest()
                ->paginate($perPage);
        } else {
            // Se é secretária, retorna notificações de todos os motoristas
            $notifications = Notification::with('route', 'driver')
                ->latest()
                ->paginate($perPage);
        }

        return response()->json($notifications, 200);
    }

    /**
     * Obter notificações por rota
     */
    public function showByRoute(Request $request, $routeId): JsonResponse
    {
        $user = $request->user();
        $route = Route::findOrFail($routeId);

        // Verificar permissão
        if ($user->is_driver && $route->driver_id !== $user->id) {
            return response()->json([
                'message' => 'Não autorizado a acessar notificações desta rota.'
            ], 403);
        }

        $notifications = $route->notifications()
            ->with('driver')
            ->latest()
            ->get();

        return response()->json($notifications, 200);
    }

    /**
     * Criar uma nova notificação (enviada pelo motorista)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Se chegou aqui é porque está autenticado via drivers, então já é motorista
        if (!$user || !($user instanceof \App\Models\Drivers)) {
            return response()->json([
                'message' => 'Apenas motoristas autenticados podem enviar notificações.'
            ], 403);
        }

        $validated = $request->validate([
            'type' => 'required|string|in:route_started,route_finished,route_delayed,vehicle_changed,route_maintenance,checkpoint_reached,driver_changed,no_transport,expense_added,route_assigned',
            'message' => 'required|string|max:500',
            'route_id' => 'required|exists:routes,id',
        ]);

        // Verificar se a rota pertence ao motorista
        $route = Route::findOrFail($validated['route_id']);
        
        // Se a rota não tem motorista atribuído, usar o motorista autenticado
        // Caso contrário, verificar se a rota pertence ao motorista
        if ($route->driver_id && $route->driver_id !== $user->id) {
            return response()->json([
                'message' => 'Você não está autorizado a enviar notificações para esta rota.'
            ], 403);
        }

        try {
            $notification = Notification::create([
                'driver_id' => $user->id,
                'route_id' => $validated['route_id'],
                'type' => $validated['type'],
                'message' => $validated['message'],
            ]);

            return response()->json([
                'message' => 'Notificação enviada com sucesso.',
                'data' => $notification->load('route', 'driver')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao enviar notificação.',
                'error' => 'notification_error'
            ], 500);
        }
    }

    /**
     * Exibir uma notificação específica
     */
    public function show(Request $request, $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $user = $request->user();

        // Verificar permissão
        if ($user->is_driver && $notification->driver_id !== $user->id) {
            return response()->json([
                'message' => 'Não autorizado a acessar esta notificação.'
            ], 403);
        }

        return response()->json($notification->load('route', 'driver'), 200);
    }

    /**
     * Marcar uma notificação como lida
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $user = $request->user();

        // Verificar permissão
        if ($user->is_driver && $notification->driver_id !== $user->id) {
            return response()->json([
                'message' => 'Não autorizado a marcar esta notificação como lida.'
            ], 403);
        }

        try {
            $notification->markAsRead();

            return response()->json([
                'message' => 'Notificação marcada como lida.',
                'data' => $notification
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao marcar notificação como lida.',
                'error' => 'update_error'
            ], 500);
        }
    }

    /**
     * Marcar todas as notificações como lidas (para o usuário autenticado)
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            if ($user->is_driver) {
                // Se é motorista, marca suas notificações
                Notification::where('driver_id', $user->id)
                    ->where('read', false)
                    ->update([
                        'read' => true,
                        'read_at' => now()
                    ]);
            } else {
                // Se é secretária, pode marcar todas como lidas
                Notification::where('read', false)
                    ->update([
                        'read' => true,
                        'read_at' => now()
                    ]);
            }

            return response()->json([
                'message' => 'Todas as notificações foram marcadas como lidas.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao marcar notificações como lidas.',
                'error' => 'update_error'
            ], 500);
        }
    }

    /**
     * Obter contagem de notificações não lidas
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->is_driver) {
            $count = Notification::where('driver_id', $user->id)
                ->where('read', false)
                ->count();
        } else {
            $count = Notification::where('read', false)->count();
        }

        return response()->json([
            'unread_count' => $count
        ], 200);
    }
}
