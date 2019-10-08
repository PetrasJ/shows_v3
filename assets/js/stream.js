function stopStream() {
    $.ajax({
        type: 'POST',
        url: window.baseUrl + 'stop',
        success: function (data) {
            console.log(data);
        }
    });
}

$(document).ready(function () {
    const listVideo = $('li.videoList');
    listVideo.click(function () {
        stopStream();
    });

    $('a, button').click(function () {
        stopStream();
    });

    let playNext = true;
    let currentVideo;
    let videoToPlay = null;
    let videoToPlayID = null;
    let count = 0;
    const videoArea = $('#video-area');

    listVideo.each(function () {
        count++;
        $(this).attr('id', count);
        if ($(this).data('movie-url') === videoToPlay) {
            play(videoToPlay, false);
            videoToPlayID = count;
        }
    });

    function video(id) {
        listVideo.removeClass('active');
        $('#' + id).addClass('active');
        currentVideo = id;
        $('.video').html(currentVideo + '/' + count);
    }

    if (videoToPlayID != null) {
        video(videoToPlayID);
    }

    listVideo.on('click', function () {
        play($(this).data('movie-url'), 'autoplay');
        video($(this).attr('id'));
    });

    play(listVideo.eq(0).data('movie-url'), 'autoplay');

    video($('#playlist li').eq(0).attr('id'));

    $('.next').click(function () {
        next();
    });

    $('.prev').click(function () {
        prev();
    });

    const videoBlock = $('video');
    videoBlock.on('ended', function () {
        if (playNext === true) next();
    });

    function next() {
        if (currentVideo !== count) video(parseInt(currentVideo) + 1);
        else {
            video(1);
            alert('Playlist ended');
        }
        let currentVideoLink = $('#' + currentVideo);

        play(currentVideoLink.data('movie-url'), 'autoplay');
    }

    function prev() {
        if (currentVideo !== 1) video(parseInt(currentVideo) - 1);
        else {
            video(count);
        }
        let currentVideoLink = $('#' + currentVideo);
        play(currentVideoLink.data('movie-url'), 'autoplay');
    }

    function play(movieUrl, autoplay) {
        videoArea.attr({
            'src': window.baseUrl + 'video/' + movieUrl,
            'poster': '',
            'autoplay': autoplay
        });

        $('#video-block').find('.title').html(movieUrl);
    }

    function playPause() {
        if (videoArea[0].paused === true) {
            videoArea[0].play();
        } else {
            videoArea[0].pause();
        }
    }

    $(document).keydown(function (e) {
        switch (e.which) {
            case 37: // left
                prev();
                break;
            case 38: // up
                prev();
                break;
            case 39: // right
                next();
                break;
            case 40: // down
                next();
                break;
            case 32: // space
                playPause();
                break;
            default:
                return; // exit this handler for other keys
        }
        e.preventDefault(); // prevent the default action (scroll / move caret)
    });
});
