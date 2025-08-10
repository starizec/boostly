<?php

namespace App\Filament\Resources\ChatResource\Pages;

use App\Filament\Resources\ChatResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Status;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action as TableAction;

class ChatInterface extends Page implements HasForms, HasActions, HasTable
{
    use InteractsWithForms;
    use InteractsWithActions;
    use InteractsWithTable;

    protected static string $resource = ChatResource::class;

    protected static string $view = 'filament.resources.chat-resource.pages.chat-interface';

    public ?Chat $selectedChat = null;
    public ?string $message = null;
    public ?string $searchQuery = '';
    public ?string $statusFilter = '';
    public bool $isLoading = false;

    public function mount(): void
    {
        // Select the first active chat by default
        $this->selectedChat = Chat::with(['contact', 'messages' => function ($query) {
            $query->with('agent')->orderBy('created_at', 'asc');
        }, 'status'])->active()->latest('last_message_at')->first();
    }

    public function refreshChats(): void
    {
        // This method will be called periodically to refresh the chat list
        if ($this->selectedChat) {
            $this->selectedChat = $this->selectedChat->fresh(['contact', 'messages' => function ($query) {
                $query->with('agent')->orderBy('created_at', 'asc');
            }, 'status']);
        }
    }

    public function handleKeyPress($key): void
    {
        if ($key === 'Enter' && !empty(trim($this->message))) {
            $this->sendMessage();
        }
    }

    public function updateChatStatus($statusId): void
    {
        if (!$this->selectedChat) {
            return;
        }

        try {
            $this->selectedChat->update(['status_id' => $statusId]);
            
            // Refresh the selected chat
            $this->selectedChat = $this->selectedChat->fresh(['contact', 'messages' => function ($query) {
                $query->with('agent')->orderBy('created_at', 'asc');
            }, 'status']);

            Notification::make()
                ->title('Chat status updated successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to update chat status')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function toggleChatStatus(): void
    {
        if (!$this->selectedChat) {
            return;
        }

        $newStatus = $this->selectedChat->status === 'active' ? 'archived' : 'active';
        $this->selectedChat->update(['status' => $newStatus]);

        Notification::make()
            ->title("Chat {$newStatus}")
            ->success()
            ->send();
    }

    public function selectChat(Chat $chat): void
    {
        $this->isLoading = true;
        
        $this->selectedChat = $chat->load(['contact', 'messages' => function ($query) {
            $query->with('agent')->orderBy('created_at', 'asc');
        }, 'status']);

        // Mark unread messages as read
        $this->selectedChat->messages()
            ->where('type', '!=', 'agent')
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        $this->isLoading = false;
    }

    public function sendMessage(): void
    {
        if (!$this->selectedChat || empty(trim($this->message))) {
            return;
        }

        try {
            $message = ChatMessage::create([
                'chat_id' => $this->selectedChat->id,
                'agent_id' => Auth::id(),
                'message' => trim($this->message),
                'type' => 'agent',
                'is_read' => false,
            ]);

            // Update chat last message time
            $this->selectedChat->update([
                'last_message_at' => now()
            ]);

            // Refresh the selected chat with new messages
            $this->selectedChat = $this->selectedChat->fresh(['contact', 'messages' => function ($query) {
                $query->with('agent')->orderBy('created_at', 'asc');
            }, 'status']);

            // Broadcast the message event
            broadcast(new MessageSent($message))->toOthers();

            $this->message = '';

            Notification::make()
                ->title('Message sent successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to send message')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Chat::query()
                    ->with(['contact', 'latestMessage'])
                    ->when($this->statusFilter, function (Builder $query, string $status) {
                        $query->where('status', $status);
                    })
                    ->when($this->searchQuery, function (Builder $query, string $search) {
                        $query->where(function (Builder $query) use ($search) {
                            $query->where('title', 'like', "%{$search}%")
                                ->orWhereHas('contact', function (Builder $query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%");
                                });
                        });
                    })
            )
            ->columns([
                TextColumn::make('contact.name')
                    ->label('Contact')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'archived' => 'gray',
                    }),
                TextColumn::make('latestMessage.message')
                    ->label('Latest Message')
                    ->limit(25)
                    ->wrap(),
                TextColumn::make('last_message_at')
                    ->label('Last Activity')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'archived' => 'Archived',
                    ])
                    ->placeholder('All Statuses'),
            ])
            ->actions([
                TableAction::make('select')
                    ->label('Select')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->action(fn (Chat $record) => $this->selectChat($record))
                    ->color('primary'),
            ])
            ->bulkActions([])
            ->defaultSort('last_message_at', 'desc')
            ->paginated(false)
            ->poll('10s');
    }

    public function getFilteredChats()
    {
        return Chat::query()
            ->with(['contact', 'latestMessage', 'status'])
            ->withCount(['messages as unread_count' => function (Builder $query) {
                $query->where('type', '!=', 'agent')->where('is_read', false);
            }])
            ->when($this->statusFilter, function (Builder $query, string $status) {
                $query->where('status', $status);
            })
            ->when($this->searchQuery, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('contact', function (Builder $query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('last_message_at')
            ->get();
    }

    public function getAvailableStatuses()
    {
        return Status::query()
            ->where('user_id', auth()->id())
            ->orWhere('company_id', auth()->user()->company_id)
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_chat')
                ->label('New Chat')
                ->icon('heroicon-o-plus')
                ->url(route('filament.admin.resources.chats.create'))
                ->openUrlInNewTab(),
        ];
    }
}
