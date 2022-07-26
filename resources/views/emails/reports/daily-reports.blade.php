@component('mail::message')
# Introduction

Your AntRank daily report is ready to download now.

@component('mail::button', ['url' => $url, 'color' => 'success'])
Download Now
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
