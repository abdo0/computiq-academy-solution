@php
    $emails = $record->emails()->with('user')->get()->map(function ($email) {
            $sender = $email->user ? $email->user->name : $email->from_address;
            $initials = $email->user ? mb_substr($email->user->name, 0, 2) : mb_substr($email->from_address, 0, 2);

            return [
                'id' => $email->id,
                'sender' => $sender,
                'to' => $email->to_address,
                'cc' => $email->cc_address,
                'body' => $email->body,
                'read' => (bool)$email->read_at,
                'initials' => strtoupper($initials)
            ];
        })->all();
        $emails = array_reverse($emails);
@endphp
<div class="space-y-4">
    @forelse ($emails as $email)
        <div class="p-4 bg-white rounded-md shadow-md dark:bg-gray-800">
            @if ($email['sender'])
                <div class="flex items-start space-x-4">
                    <div class="relative flex-shrink-0">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-500 dark:bg-gray-700">
                            <span class="font-medium leading-none text-white">{{ $email['initials'] }}</span>
                        </span>
                        @if (!$email['read'])
                            <span class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white dark:ring-gray-800"></span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $email['sender'] }}</p>
                                @if ($email['to'])
                                    <p class="text-sm text-gray-500 dark:text-gray-400">To: {{ $email['to'] }}</p>
                                @endif
                                @if ($email['cc'])
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Cc: {{ $email['cc'] }}</p>
                                @endif
                            </div>
                            @if (!$email['read'])
                                <button type="button" wire:click="markAsRead({{ $email['id'] }})" wire:loading.attr="disabled" wire:target="markAsRead({{ $email['id'] }})" class="text-sm text-primary-600 hover:underline focus:outline-none">
                                    <span wire:loading.remove wire:target="markAsRead({{ $email['id'] }})">Mark as read</span>
                                    <svg wire:loading wire:target="markAsRead({{ $email['id'] }})" class="animate-spin h-4 w-4 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if ($email['body'])
                <div class="mt-4 text-gray-700 dark:text-gray-300">
                    {!! $email['body'] !!}
                </div>
            @endif
        </div>
    @empty
        <div class="p-4 text-center text-gray-500 dark:text-gray-400">
            No emails to display.
        </div>
    @endforelse

    <div class="flex justify-start space-x-2 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button type="button" wire:click="reply" wire:loading.attr="disabled" wire:target="reply" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
            <svg wire:loading wire:target="reply" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg wire:loading.remove wire:target="reply" class="w-4 h-4 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536L16.732 3.732z"></path></svg>
            Reply
        </button>
        <button type="button" wire:click="markAllRead" wire:loading.attr="disabled" wire:target="markAllRead" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-offset-gray-800">
            <span wire:loading.remove wire:target="markAllRead">Mark all read</span>
            <svg wire:loading wire:target="markAllRead" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </button>
    </div>

    @if ($this->isReplying)
        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <form wire:submit.prevent="sendReply">
                <div>
                    <label for="replyMessage" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Your Reply</label>
                    <div class="mt-1">
                        <textarea id="replyMessage" wire:model.defer="replyMessage" rows="4" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:border-primary-500 sm:text-sm"></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" wire:click="$set('isReplying', false)" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-offset-gray-800">
                        Cancel
                    </button>
                    <button type="button" wire:click="sendReply" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
    @endif
</div> 