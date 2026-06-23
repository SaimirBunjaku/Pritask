@foreach ($comments as $comment)
    @include('issues.partials.comment', ['comment' => $comment])
@endforeach
