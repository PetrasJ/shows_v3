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
            videoArea.attr({
                'src': window.baseUrl + 'video/' + videoToPlay,
            });
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
        videoArea.attr({
            'src': window.baseUrl + 'video/' + $(this).data('movie-url'),
            'poster': '',
            'autoplay': 'autoplay'
        });
        video($(this).attr('id'));
    });

    videoArea.attr({
        'src': window.baseUrl + 'video/' + listVideo.eq(0).data('movie-url'),
        'autoplay': 'autoplay'
    });

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

        videoArea.attr({
            'src': window.baseUrl + 'video/' + currentVideoLink.data('movie-url'),
            'poster': '',
            'autoplay': 'autoplay'
        });
    }

    function prev() {
        if (currentVideo !== 1) video(parseInt(currentVideo) - 1);
        else {
            video(count);
        }
        let currentVideoLink = $('#' + currentVideo);
        videoArea.attr({
            'src': window.baseUrl + 'video/' + currentVideoLink.data('movie-url'),
            'poster': '',
            'autoplay': 'autoplay'
        });
    }

    function play() {
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
                play();
                break;
            default:
                return; // exit this handler for other keys
        }
        e.preventDefault(); // prevent the default action (scroll / move caret)
    });
});
