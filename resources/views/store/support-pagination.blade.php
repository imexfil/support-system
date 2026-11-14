@if($pages->hasPages())
<nav class="pagination" aria-label="{{ __('Pagination') }}">
    @if($pages->previousPageUrl())<a class="button" href="{{ $pages->previousPageUrl() }}">{{ __('Previous') }}</a>@endif
    @if($pages->nextPageUrl())<a class="button" href="{{ $pages->nextPageUrl() }}">{{ __('Next') }}</a>@endif
</nav>
@endif
