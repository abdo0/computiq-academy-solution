<?php

namespace App\Services;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;

class DatabaseNotificationService
{
    /**
     * Send a database notification to all users in the same branch
     *
     * @param  int  $branchId  The branch ID
     * @param  string  $title  The notification title
     * @param  string  $body  The notification body with placeholders
     * @param  array  $bodyData  Data to replace placeholders in body
     * @param  string  $status  Notification status (success, warning, danger, info)
     * @param  string  $icon  The notification icon
     * @param  array  $actions  Optional notification actions
     * @param  mixed  $creator  The creator user object for avatar
     */
    public static function sendToAll(
        string $title,
        string $body,
        array $bodyData = [],
        string $status = 'success',
        string $icon = 'heroicon-o-bell',
        array $actions = [],
        mixed $creator = null
    ): void {
        // Get all active users, excluding the authenticated user
        $users = User::withoutGlobalScopes()
            ->select('id')
            ->where('is_active', true)
            ->when(Auth::check(), function ($query) {
                return $query->where('id', '!=', Auth::id());
            })
            ->get();

        // Context::add('branch_id_for_notification', $branchId); // Removed

        // Create notification with status method
        $notification = Notification::make()
            ->title($title)
            ->body($body, $bodyData);

        // Apply status-specific styling
        match ($status) {
            'success' => $notification->success(),
            'warning' => $notification->warning(),
            'danger' => $notification->danger(),
            'info' => $notification->info(),
            default => $notification->success(),
        };

        // Set icon and send notification
        $notification
            ->icon($icon);

        // Add actions if provided
        if (! empty($actions)) {
            $notification->actions($actions);
        }

        $notification->sendToDatabase($users, isEventDispatched: true);

        // Remove branch ID from context
        // Context::forget('branch_id_for_notification'); // Removed
    }

    /**
     * Send a notification for a created record
     *
     * @param  mixed  $record  The created record
     * @param  string  $modelType  The model name (e.g., 'Ticket', 'Project')
     * @param  string  $titleField  The field to use for the title (e.g., 'subject', 'name', 'title')
     * @param  string  $icon  The notification icon
     * @param  string  $status  Notification status
     * @param  string|null  $resourceClass  The Filament resource class for URL generation
     */
    public static function sendCreatedNotification(
        mixed $record,
        string $modelType,
        string $titleField,
        string $icon,
        string $status = 'success',
        ?string $resourceClass = null
    ): void {
        $title = __(':modelType Created', ['modelType' => __($modelType)]);
        $body = self::generateNotificationBody($modelType, $record->$titleField);

        self::sendNotificationWithLayout(
            branchId: $record->branch_id,
            title: $title,
            body: $body,
            bodyData: [
                $titleField => $record->$titleField,
                'creator' => self::getCreatorName(),
            ],
            status: $status,
            icon: $icon,
            resourceClass: $resourceClass,
            record: $record
        );
    }

    /**
     * Send a general notification for model updates (status changes, etc.)
     *
     * @param  mixed  $record  The updated record
     * @param  string  $modelType  The model type (e.g., 'Ticket', 'Project', 'Task')
     * @param  string  $action  The action performed (e.g., 'updated status', 'assigned', 'completed')
     * @param  string  $titleField  The field to use for the title (e.g., 'subject', 'name', 'title')
     * @param  string  $icon  The notification icon
     * @param  string  $status  Notification status
     * @param  string|null  $resourceClass  The Filament resource class for URL generation
     * @param  array  $additionalData  Additional data for the notification
     */
    public static function sendGeneralNotification(
        mixed $record,
        string $modelType,
        string $action,
        string $titleField,
        string $icon,
        string $status = 'info',
        ?string $resourceClass = null,
        array $additionalData = []
    ): void {
        $title = __(':modelType | :action', ['modelType' => __($modelType), 'action' => __($action)]);
        $body = self::generateGeneralNotificationBody($modelType, $action, $record->$titleField, $additionalData);

        self::sendNotificationWithLayout(
            branchId: $record->branch_id,
            title: $title,
            body: $body,
            bodyData: array_merge([
                $titleField => $record->$titleField,
                'creator' => self::getCreatorName(),
            ], $additionalData),
            status: $status,
            icon: $icon,
            resourceClass: $resourceClass,
            record: $record
        );

    }

    /**
     * Generate general notification body template with avatar
     */
    private static function generateGeneralNotificationBody(string $modelType, string $action, string $titleValue, array $additionalData = []): string
    {
        $creator = Auth::user();
        $avatarHtml = self::generateAvatarHtml($creator);

        // Build the message with placeholders
        $messageTemplate = __('<b>:creator</b> has :action :modelType <b>:modelName</b>', [
            'creator' => self::getCreatorName(),
            'modelType' => __($modelType),
            'modelName' => $titleValue,
            'action' => __($action),
        ]);

        // Add additional data if provided
        if (! empty($additionalData)) {
            foreach ($additionalData as $key => $value) {
                $messageTemplate = str_replace(":{$key}", $value, $messageTemplate);
            }
        }

        // Add comment information if available
        $commentHtml = '';
        if (! empty($additionalData['comment'])) {
            $commentHtml = '<div class="mt-2 text-sm text-gray-600 dark:text-gray-400">'.$additionalData['comment'].'</div>';
        }

        return '<div class="flex items-start gap-2">'.$avatarHtml.'<div><span>'.$messageTemplate.'</span>'.$commentHtml.'</div></div>';
    }

    /**
     * Generate unified notification body template with avatar
     */
    private static function generateNotificationBody(string $modelType, string $titleValue): string
    {
        $creator = Auth::user();
        $avatarHtml = self::generateAvatarHtml($creator);
        $message = __('<b> :creator </b> has created :modelType <b> :modelName </b>', [
            'creator' => self::getCreatorName(),
            'modelType' => __($modelType),
            'modelName' => $titleValue,
        ]);

        return '<div class="flex items-start gap-2">'.$avatarHtml.'<span>'.$message.'</span></div>';
    }

    /**
     * Get the current creator's name
     */
    private static function getCreatorName(): string
    {
        $creator = Auth::user();

        return $creator?->name ?? __('System');
    }

    /**
     * Send notification with proper layout and actions
     */
    private static function sendNotificationWithLayout(
        string $title,
        string $body,
        array $bodyData,
        string $status,
        string $icon,
        ?string $resourceClass,
        mixed $record
    ): void {
        $creator = Auth::user();
        $actions = self::generateNotificationActions($resourceClass, $record);

        self::sendToAll(
            title: $title,
            body: $body,
            bodyData: $bodyData,
            status: $status,
            icon: $icon,
            actions: $actions,
            creator: $creator
        );
    }

    /**
     * Generate notification actions (View button only)
     */
    private static function generateNotificationActions(?string $resourceClass, mixed $record): array
    {
        $actions = [];
        if (! $resourceClass || ! class_exists($resourceClass)) {
            return $actions;
        }

        try {
            $viewUrl = $resourceClass::getUrl('view', ['record' => $record]);
            $actions[] = \Filament\Actions\Action::make('view')
                ->label(__('View'))
                ->url($viewUrl)
                ->icon('heroicon-o-eye');

        } catch (\Exception $e) {
        }

        try {
            $editUrl = $resourceClass::getUrl('edit', ['record' => $record]);
            $actions[] = \Filament\Actions\Action::make('edit')
                ->label(__('Edit'))
                ->url($editUrl)
                ->icon('heroicon-o-pencil');
        } catch (\Exception $e) {
        }

        return $actions;
    }

    /**
     * Generate avatar HTML for notifications
     *
     * @param  mixed  $creator  The creator user object
     * @return string HTML img tag for avatar
     */
    private static function generateAvatarHtml(mixed $creator): string
    {
        if (! $creator || ! method_exists($creator, 'getFirstMediaUrl')) {
            return self::generateInitialsAvatar('U');
        }

        $avatarUrl = $creator->getFirstMediaUrl('avatar');
        if ($avatarUrl) {
            return '<img src="'.$avatarUrl.'" alt="'.($creator->name ?? 'User').'" class="w-8 h-8 rounded-full flex-shrink-0" />';
        }

        // Generate initials avatar
        return self::generateInitialsAvatar($creator->name ?? 'U');
    }

    /**
     * Generate initials avatar HTML for users without profile pictures
     *
     * @param  string  $name  The user's name
     * @return string HTML img tag with initials avatar
     */
    private static function generateInitialsAvatar(string $name): string
    {
        // Extract initials from name
        $words = explode(' ', trim($name));
        $initials = '';

        if (count($words) >= 2) {
            // Use first letter of first and last word
            $initials = strtoupper(substr($words[0], 0, 1).substr(end($words), 0, 1));
        } else {
            // Use first two letters of single word
            $initials = strtoupper(substr($name, 0, 2));
        }

        // Generate random background color
        $colors = [
            '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16',
            '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9',
            '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef',
            '#ec4899', '#f43f5e',
        ];
        $bgColor = $colors[array_rand($colors)];

        // Create SVG avatar
        $svg = '<svg width="32" height="32" xmlns="http://www.w3.org/2000/svg">
            <circle cx="16" cy="16" r="16" fill="'.$bgColor.'"/>
            <text x="16" y="20" font-family="Arial, sans-serif" font-size="12" font-weight="bold" 
                  text-anchor="middle" fill="white">'.$initials.'</text>
        </svg>';

        $dataUrl = 'data:image/svg+xml;base64,'.base64_encode($svg);

        return '<img src="'.$dataUrl.'" alt="'.$name.'" class="w-8 h-8 rounded-full flex-shrink-0" />';
    }
}
