<?php
$seenIcon = !!$seen ? 'check-double' : 'check';
$timeAndSeen =
    "<span data-time='$created_at' class='message-time'>
        " .
    ($sent_by == 'admin' ? "<span class='fas fa-$seenIcon' seen'></span>" : '') .
    " <span class='time'>$timeAgo</span>
    </span>";
$audioId = substr(md5(uniqid(mt_rand(), true)), 0, 8);
$messenger_color = optional(App\Models\User::first())->messenger_color;
$senderColor = $messenger_color ?: Chatify::getFallbackColor();
$attachmentUrl = null;
if (!empty(@$attachment->file)) {
    $attachmentUrl = filter_var($attachment->file, FILTER_VALIDATE_URL)
        ? $attachment->file
        : route(config('chatify.attachments.download_route_name'), ['fileName' => $attachment->file]);
}
?>

<div class="message-card @if ($sent_by == 'admin') mc-sender @endif" data-id="{{ $id }}">
    {{-- Delete Message Button --}}
    @if ($sent_by == 'admin')
        <div class="actions">
            <i class="fas fa-trash delete-btn" data-id="{{ $id }}"></i>
        </div>
    @endif
    {{-- Card --}}
    <div class="message-card-content">
        @if (!in_array(@$attachment->type, ['image', 'audio']) || $message)
            <div class="message">
                {!! $message == null && $attachment != null && @$attachment->type != 'file'
                    ? $attachment->title
                    : nl2br($message) !!}
                {!! $timeAndSeen !!}
                {{-- If attachment is a file --}}
                @if (@$attachment->type == 'file')
                    <a href="{{ $attachmentUrl }}"
                        class="file-download">
                        <span class="fas fa-file"></span> {{ $attachment->title }}</a>
                @endif
            </div>
        @endif
        @if (@$attachment->type == 'image')
            <div class="image-wrapper" style="text-align: {{ $sent_by == 'admin' ? 'end' : 'start' }}">
                <div class="image-file chat-image"
                    style="background-image: url('{{ Chatify::getAttachmentUrl($attachment->file) }}')">
                    <div>{{ $attachment->title }}</div>
                </div>
                <div style="margin-bottom:5px">
                    {!! $timeAndSeen !!}
                </div>
            </div>
        @endif
        @if (@$attachment->type == 'audio')
            <div class="message voice-message-bubble">
                <div class="voice-message-player" data-audio-url="{{ $attachmentUrl }}">
                    <button class="voice-message-play" type="button" onclick="window.playChatifyVoice(this)">
                        <span class="fas fa-play"></span>
                    </button>
                    <audio class="voice-message-audio" src="{{ $attachmentUrl }}" preload="metadata"></audio>
                </div>
                <div class="voice-message-meta">
                    {!! $timeAndSeen !!}
                </div>
            </div>
        @endif
    </div>
</div>
@if ($attachment->type == 'audio')
    <script>
        window.playChatifyVoice = window.playChatifyVoice || function (button) {
            const player = button.closest('.voice-message-player');
            const audio = player && player.querySelector('.voice-message-audio');
            const icon = button.querySelector('span');
            if (!audio) return;

            if (audio.paused) {
                audio.play().then(() => {
                    if (icon) icon.className = 'fas fa-pause';
                }).catch((error) => console.error('Chatify audio playback failed:', error));
            } else {
                audio.pause();
                if (icon) icon.className = 'fas fa-play';
            }

            audio.onended = () => {
                if (icon) icon.className = 'fas fa-play';
            };
        };
    </script>
@endif
