@component('emails.layouts.main', ['title' => $title ?? '', 'subtitle' => $subtitle ?? ''])
    {!! $body ?? '' !!}
@endcomponent

