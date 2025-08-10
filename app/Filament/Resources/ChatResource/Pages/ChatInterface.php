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
use App\Models\Tag;

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
    
    // Contact editing properties
    public ?string $editingContactName = null;
    public ?string $editingContactEmail = null;
    public ?string $editingContactPhone = null;
    
    // Note editing properties
    public ?string $editingNote = null;

    public function mount(): void
    {
        // Select the first active chat by default
        $this->selectedChat = Chat::with(['contact', 'messages' => function ($query) {
            $query->with('agent')->orderBy('created_at', 'asc');
        }, 'status', 'tags'])->active()->latest('last_message_at')->first();
        
        // Initialize editingNote with current note value if chat is selected
        if ($this->selectedChat) {
            $this->editingNote = $this->selectedChat->note ?? '';
            // Initialize contact fields
            if ($this->selectedChat->contact) {
                $this->editingContactName = $this->selectedChat->contact->name ?? '';
                $this->editingContactEmail = $this->selectedChat->contact->email ?? '';
                $this->editingContactPhone = $this->selectedChat->contact->phone ?? '';
            }
        }
    }

    public function refreshChats(): void
    {
        // This method will be called periodically to refresh the chat list
        if ($this->selectedChat) {
            $this->selectedChat = $this->selectedChat->fresh(['contact', 'messages' => function ($query) {
                $query->with('agent')->orderBy('created_at', 'asc');
            }, 'status', 'tags']);
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







    private function formatPhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-digit characters except +, (, ), -, and spaces
        $cleaned = preg_replace('/[^\d\s\-\+\(\)]/', '', $phone);
        
        // Trim whitespace
        $cleaned = trim($cleaned);
        
        return empty($cleaned) ? null : $cleaned;
    }

    private function validateEmailDomain(?string $email): bool
    {
        if (empty($email)) {
            return true; // Empty email is valid (optional field)
        }

        $domain = substr(strrchr($email, '@'), 1);
        if (!$domain) {
            return false; // No @ symbol
        }

        // Check if domain has a valid TLD
        return filter_var($domain, FILTER_VALIDATE_DOMAIN) !== false;
    }

    public function saveContactChanges(): void
    {
        if (!$this->selectedChat || !$this->selectedChat->contact) {
            return;
        }

        // Check if user has permission to edit contacts
        if (!auth()->user()->hasAnyRole(['admin', 'agent', 'manager'])) {
            Notification::make()
                ->title('Permission Denied')
                ->body('You do not have permission to edit contact information')
                ->danger()
                ->send();
            return;
        }

        // Basic validation
        if (empty(trim($this->editingContactName))) {
            Notification::make()
                ->title('Validation Error')
                ->body('Contact name is required')
                ->danger()
                ->send();
            return;
        }

        if (strlen(trim($this->editingContactName)) > 255) {
            Notification::make()
                ->title('Validation Error')
                ->body('Contact name is too long (maximum 255 characters)')
                ->danger()
                ->send();
            return;
        }

        if (!empty($this->editingContactEmail) && !filter_var($this->editingContactEmail, FILTER_VALIDATE_EMAIL)) {
            Notification::make()
                ->title('Validation Error')
                ->body('Please enter a valid email address')
                ->danger()
                ->send();
            return;
        }

        if (!empty($this->editingContactEmail) && !$this->validateEmailDomain($this->editingContactEmail)) {
            Notification::make()
                ->title('Validation Error')
                ->body('Please enter a valid email address with a valid domain')
                ->danger()
                ->send();
            return;
        }

        if (!empty($this->editingContactEmail) && strlen(trim($this->editingContactEmail)) > 255) {
            Notification::make()
                ->title('Validation Error')
                ->body('Email address is too long (maximum 255 characters)')
                ->danger()
                ->send();
            return;
        }

        if (!empty($this->editingContactPhone) && !preg_match('/^[\d\s\-\+\(\)\.]+$/', $this->editingContactPhone)) {
            Notification::make()
                ->title('Validation Error')
                ->body('Phone number contains invalid characters')
                ->danger()
                ->send();
            return;
        }

        if (!empty($this->editingContactPhone) && strlen(trim($this->editingContactPhone)) > 20) {
            Notification::make()
                ->title('Validation Error')
                ->body('Phone number is too long (maximum 20 characters)')
                ->danger()
                ->send();
            return;
        }

        if (empty(trim($this->editingContactEmail)) && empty(trim($this->editingContactPhone))) {
            Notification::make()
                ->title('Validation Error')
                ->body('Please provide either an email address or phone number')
                ->danger()
                ->send();
            return;
        }

        try {
            $this->selectedChat->contact->update([
                'name' => trim($this->editingContactName),
                'email' => !empty(trim($this->editingContactEmail)) ? trim($this->editingContactEmail) : null,
                'phone' => $this->formatPhoneNumber($this->editingContactPhone),
            ]);

            // Refresh the selected chat
            $this->selectedChat = $this->selectedChat->fresh(['contact', 'messages' => function ($query) {
                $query->with('agent')->orderBy('created_at', 'asc');
            }, 'status']);

            // Keep the contact fields populated with the saved values instead of resetting them
            $this->editingContactName = $this->selectedChat->contact->name ?? '';
            $this->editingContactEmail = $this->selectedChat->contact->email ?? '';
            $this->editingContactPhone = $this->selectedChat->contact->phone ?? '';

            Notification::make()
                ->title('Contact information updated successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to update contact information')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }



    public function saveNoteChanges(): void
    {
        if (!$this->selectedChat) {
            return;
        }

        try {
            $this->selectedChat->update([
                'note' => !empty(trim($this->editingNote)) ? trim($this->editingNote) : null,
            ]);

            // Refresh the selected chat
            $this->selectedChat = $this->selectedChat->fresh(['contact', 'messages' => function ($query) {
                $query->with('agent')->orderBy('created_at', 'asc');
            }, 'status', 'tags']);

            // Keep the editingNote populated with the saved value instead of resetting it
            $this->editingNote = $this->selectedChat->note ?? '';

            Notification::make()
                ->title('Note updated successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to update note')
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
        
        // Reset note editing state if selecting a different chat
        if ($this->selectedChat && $this->selectedChat->id !== $chat->id) {
            $this->editingNote = null;
        }
        
        $this->selectedChat = $chat->load(['contact', 'messages' => function ($query) {
            $query->with('agent')->orderBy('created_at', 'asc');
        }, 'status', 'tags']);

        // Initialize editingNote with current note value
        $this->editingNote = $this->selectedChat->note ?? '';
        
        // Initialize contact fields
        if ($this->selectedChat->contact) {
            $this->editingContactName = $this->selectedChat->contact->name ?? '';
            $this->editingContactEmail = $this->selectedChat->contact->email ?? '';
            $this->editingContactPhone = $this->selectedChat->contact->phone ?? '';
        }

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
            }, 'status', 'tags']);

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
                    ->with(['contact', 'latestMessage', 'tags'])
                    ->when($this->statusFilter, function (Builder $query, string $status) {
                        $query->where('status', $status);
                    })
                    ->when($this->searchQuery, function (Builder $query, string $search) {
                        $query->where(function (Builder $query) use ($search) {
                            $query->orWhereHas('contact', function (Builder $query) use ($search) {
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
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'archived' => 'gray',
                    }),
                TextColumn::make('tags')
                    ->label('Tags')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(function ($state) {
                        if (!$state || $state->isEmpty()) {
                            return 'No tags';
                        }
                        return $state->take(2)->pluck('name')->join(', ') . 
                               ($state->count() > 2 ? ' +' . ($state->count() - 2) : '');
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
            ->with(['contact', 'latestMessage', 'status', 'tags'])
            ->withCount(['messages as unread_count' => function (Builder $query) {
                $query->where('type', '!=', 'agent')->where('is_read', false);
            }])
            ->when($this->statusFilter, function (Builder $query, string $status) {
                $query->where('status', $status);
            })
            ->when($this->searchQuery, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->orWhereHas('contact', function (Builder $query) use ($search) {
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

    public function getAvailableTags()
    {
        return Tag::query()
            ->where('user_id', auth()->id())
            ->orWhere('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name', 'color'])
            ->mapWithKeys(function ($tag) {
                return [$tag->id => [
                    'name' => $tag->name,
                    'color' => $tag->color ?? '#6b7280'
                ]];
            })
            ->toArray();
    }

    /**
     * Get the list of agents that responded to the selected chat
     */
    public function getChatAgents()
    {
        if (!$this->selectedChat) {
            return collect();
        }

        return $this->selectedChat->messages()
            ->where('type', 'agent')
            ->whereNotNull('agent_id')
            ->with('agent:id,name,email')
            ->get()
            ->pluck('agent')
            ->unique('id')
            ->filter()
            ->values();
    }

    public function updateChatTags($tagIds)
    {
        if (!$this->selectedChat) {
            return;
        }

        try {
            // Convert string to array if needed
            if (is_string($tagIds)) {
                $tagIds = json_decode($tagIds, true) ?? [];
            }

            // Sync the tags
            $this->selectedChat->tags()->sync($tagIds);
            
            // Refresh the selected chat
            $this->selectedChat = $this->selectedChat->fresh(['contact', 'messages' => function ($query) {
                $query->with('agent')->orderBy('created_at', 'asc');
            }, 'status', 'tags']);

            Notification::make()
                ->title('Chat tags updated successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to update chat tags')
                ->body('An error occurred while updating the chat tags.')
                ->danger()
                ->send();
        }
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
