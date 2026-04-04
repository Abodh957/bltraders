<x-mail::message>
Hello!

{{$data['message']}}

<x-mail::button :url="$data['url']">
{{$data['buttonName']}}
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
