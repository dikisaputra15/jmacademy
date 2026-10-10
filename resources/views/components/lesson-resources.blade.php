@props(['lesson'])
@if(filled($lesson->resources))
<div class="lesson-resources p-3" style="overflow-wrap:anywhere">
    {!! \App\Support\LessonResources::render($lesson->resources) !!}
</div>
@endif
