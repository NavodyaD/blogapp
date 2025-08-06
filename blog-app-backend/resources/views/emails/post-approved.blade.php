<!DOCTYPE html>
<html>
<head>
    <title>Post Approved</title>
</head>
<body>
    <h3>Hello {{ $post->user->name }}</h3>
    <p>Your blog post titled "{{ $post->post_title }}" has been approved and is now live on BlogApp, users can view, react, comment on your blog.</p>
    <p>Explore more on blogposts and gain more user engagement. Thank you for posting on BlogApp.</p>
    <p>Best Regards,</p>
    <p>BlogApp Team.</p>
</body>
</html>
