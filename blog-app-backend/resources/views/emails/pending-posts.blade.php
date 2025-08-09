<!DOCTYPE html>
<html>
<head>
    <title>Approve and publish Pending Posts</title>
</head>
<body>
    <h3>Hello Admin,</h3>
    <p>There are {{ $pendingCount }} posts submitted by writers are pending for approval. Approve and publish the pending blogposts to live them on BlogApp.</p>

    {{--
    @if ($pendingTitles->isNotEmpty())
        <ul>
            @foreach ($pendingTitles as $title)
                <li>{{ $title }}</li>
            @endforeach
        </ul>
    @else
        <p>No pending blog posts found.</p>
    @endif
    --}}
    <p>Best Regards,</p>
    <p>BlogApp.</p>
</body>
</html>
