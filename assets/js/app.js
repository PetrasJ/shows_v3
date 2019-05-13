require('../css/app.css');
const $ = require('jquery');
window.$ = $;
require('bootstrap');

require('jquery-ui/ui/widgets/autocomplete');

const app = {
    init: function () {
        this.initSearch();
        this.initModals();
        this.initUnwatchedEpisodes();
    },
    initSearch: function () {
        $('#search_show_search').autocomplete({
            source: window.baseUrl + 'search',
            minLength: 2,
            delay: 100,
            focus: function (event) {
                event.preventDefault();
            },
            select: function (event, ui) {
                alert(ui.item.value);
            }
        });
    },
    initModals: function () {
        const t = this;
        $('#show-settings').on('shown.bs.modal', function (e) {
            const button = $(e.relatedTarget);
            const modal = $(this);
            modal.find('.modal-title').html(button.data('title'));
            modal.find('.show-offset').val(button.data('offset')).attr('data-id', button.data('id'));
            t.initSettings();
        })
    },
    initSettings: function () {
        $('.show-offset').change(function () {
            $.ajax({
                type: 'post',
                url: window.baseUrl + 'shows/update',
                data: {
                    id: $(this).data('id'),
                    value: $(this).val()
                },
                success: function (data) {
                    console.log(data);
                }
            }).fail(function () {
                console.log('fail');
            });

        })
    },
    initUnwatchedEpisodes: function () {
        const t = this;
        $('.unwatched-episodes').unbind().on('click', function () {
            $.ajax({
                type: 'get',
                url: $(this).data('link'),
                success: (data) => {
                    $('#result').html(data);
                    t.initWatchActions()
                }
            }).fail(function () {
                console.log('fail');
            });

        })
    },
    initWatchActions: function () {
        $('.watch-episode').unbind().on('click', function () {
            const id = $(this).data('id');
            $.ajax({
                type: 'post',
                url: window.baseUrl + 'unwatched/watch',
                data: {
                    id: id,
                },
                success: (data) => {
                    console.log(data);
                    $('#' + id).hide();
                }
            }).fail(function () {
                console.log('fail');
            });
        });

        $('.comment-episode').unbind().submit(function (e) {
            e.preventDefault();
            $.ajax({
                type: 'post',
                url: window.baseUrl + 'unwatched/comment',
                data: {
                    id: $(this).data('id'),
                    comment: $(this).find('.comment').val()
                },
                success: (data) => {
                    console.log(data);
                }
            }).fail(function () {
                console.log('fail');
            });
        });
    }
};

$(document).ready(function () {
    app.init();
});
