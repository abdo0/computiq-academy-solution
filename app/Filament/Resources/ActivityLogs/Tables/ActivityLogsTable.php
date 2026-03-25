<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Enums\ActivityAction;
use App\Filament\Exports\ActivityLogExporter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                // Primary information - what happened
                TextColumn::make('rendered_message')
                    ->label(__('Activity'))
                    ->icon(fn ($record) => $record->action_icon)
                    ->color(fn ($record) => $record->action_color)
                    ->searchable(false)
                    ->sortable(false)
                    ->wrap()
                    ->weight('bold'),

                // Where it happened (branch context)

                // Who performed the action
                TextColumn::make('user.name')
                    ->label(__('User'))
                    ->icon(Heroicon::User)
                    ->color(fn ($record) => $record->user ? 'primary' : 'gray')
                    ->badge(fn ($record) => ! $record->user)
                    ->sortable(query: fn ($query, string $direction) => $query->orderByJsonRelation('user.name', $direction))
                    ->searchable()
                    ->toggleable(),

                // When it happened
                TextColumn::make('created_at')
                    ->label(__('Time'))
                    ->dateTime()
                    ->icon(Heroicon::Clock)
                    ->color('info')
                    ->sortable()
                    ->toggleable()
                    ->since(),

                // Action type (with visual indicators)
                TextColumn::make('action')
                    ->label(__('Action Type'))
                    ->badge()
                    ->icon(fn ($record) => $record->action_icon)
                    ->color(fn ($record) => $record->action_color)
                    ->formatStateUsing(fn ($record) => $record->action_label)
                    ->toggleable()
                    ->sortable(),

                ViewColumn::make('changes_summary')
                    ->label(__('Changes'))
                    ->view('filament.resources.activity-log-resource.columns.changes-summary')
                    ->alignCenter()
                    ->toggleable(),

                // What model was affected
                TextColumn::make('model_type')
                    ->label(__('Model'))
                    ->formatStateUsing(fn (?string $state): string => $state ? __(class_basename($state)) : __('-'))
                    ->icon(fn ($record) => match (class_basename($record->model_type ?? '')) {
                        'User' => Heroicon::User,
                        'Customer' => Heroicon::Users,
                        'Employee' => Heroicon::UserGroup,
                        'Task' => Heroicon::ClipboardDocumentList,
                        'Project' => Heroicon::Folder,
                        'Contract' => Heroicon::DocumentText,
                        'Opportunity' => Heroicon::LightBulb,
                        'Ticket' => Heroicon::Ticket,

                        'Client' => Heroicon::UserCircle,
                        'Complaint' => Heroicon::ExclamationTriangle,
                        'MarketingCampaign' => Heroicon::Megaphone,
                        default => Heroicon::DocumentText
                    })
                    ->color(fn ($record) => match (class_basename($record->model_type ?? '')) {
                        'User' => 'primary',
                        'Customer' => 'success',
                        'Employee' => 'info',
                        'Task' => 'warning',
                        'Project' => 'purple',
                        'Contract' => 'gray',
                        'Opportunity' => 'warning',
                        'Ticket' => 'danger',

                        'Client' => 'success',
                        'Complaint' => 'danger',
                        'MarketingCampaign' => 'purple',
                        default => 'gray'
                    })
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                // Model ID for reference
                TextColumn::make('model_id')
                    ->label(__('Record ID'))
                    ->icon(Heroicon::Hashtag)
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                // Technical details (hidden by default)
                TextColumn::make('ip_address')
                    ->label(__('IP Address'))
                    ->icon(Heroicon::GlobeAlt)
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user_agent')
                    ->label(__('User Agent'))
                    ->icon(Heroicon::ComputerDesktop)
                    ->color('gray')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter by branch first (most important for organization)

                // Filter by action type
                SelectFilter::make('action')
                    ->label(__('Action Type'))
                    ->options(ActivityAction::options()),

                // Filter by model type
                SelectFilter::make('model_type')
                    ->label(__('Model Type'))
                    ->options(function () {
                        return \App\Models\ActivityLog::distinct('model_type')
                            ->whereNotNull('model_type')
                            ->pluck('model_type')
                            ->mapWithKeys(function ($type) {
                                $basename = class_basename($type);

                                return [$type => __($basename)];
                            })
                            ->toArray();
                    }),

                // Filter by user
                SelectFilter::make('user_id')
                    ->label(__('User'))
                    ->relationship('user', 'name', fn ($query) => $query->orderBy('name'))
                    ->searchableBy('name', \App\Models\User::class),
            ])
            ->filtersFormColumns(4)
            ->deferFilters(false)
            ->recordActions([
                Action::make('view_changes')
                    ->label(__('Changes'))
                    ->icon(Heroicon::ListBullet)
                    ->iconButton()
                    ->tooltip(__('View Changes'))
                    ->modalHeading(fn ($record) => __('Changes for :model', ['model' => $record->rendered_message]))
                    ->modalContent(fn ($record) => view('filament.resources.activity-log-resource.changes-modal', [
                        'record' => $record,
                        'changes' => $record->changes,
                        'original' => $record->original,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('Close'))
                    ->visible(fn ($record) => ! empty($record->changes) || $record->action->value === 'created'),

                Action::make('view')
                    ->hidden()
                    ->icon(Heroicon::Eye)
                    ->label(__('View'))
                    ->iconButton()
                    ->url(fn ($record) => route('filament.admin.resources.activity-logs.view', $record))
                    ->tooltip(__('View Details')),
            ])
            // ->recordAction('view_changes')
            ->recordUrl(null)
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label(__('Export Selected'))
                        ->icon(Heroicon::ArrowDownTray)
                        ->color('primary')
                        ->exporter(ActivityLogExporter::class)
                        ->fileName(fn () => 'activity-logs-selected-'.now()->format('Y-m-d-H-i-s'))
                        ->formats([
                            ExportFormat::Xlsx,
                            ExportFormat::Csv,
                        ])
                        ->columnMappingColumns(3), // 3-column layout for column selection
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordClasses(fn ($record) => match (class_basename($record->model_type ?? '')) {
                'User' => 'bg-blue-50 dark:bg-blue-900/20',
                'Customer' => 'bg-green-50 dark:bg-green-900/20',
                'Employee' => 'bg-cyan-50 dark:bg-cyan-900/20',
                'Task' => 'bg-yellow-50 dark:bg-yellow-900/20',
                'Project' => 'bg-purple-50 dark:bg-purple-900/20',
                'Contract' => 'bg-gray-50 dark:bg-gray-900/20',
                'Opportunity' => 'bg-orange-50 dark:bg-orange-900/20',
                'Ticket' => 'bg-red-50 dark:bg-red-900/20',

                'Client' => 'bg-teal-50 dark:bg-teal-900/20',
                'Complaint' => 'bg-rose-50 dark:bg-rose-900/20',
                'MarketingCampaign' => 'bg-violet-50 dark:bg-violet-900/20',
                default => 'bg-gray-50 dark:bg-gray-900/20'
            });
    }
}
