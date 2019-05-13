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
        $('#search_show').autocomplete({
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
            }).fail(function (data) {
                console.log(data);
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
                    $('#result').hide().html(data).slideDown();
                    t.initWatchActions()
                }
            }).fail(function (data) {
                console.log(data);
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
                success: () => {
                    $('#' + id).slideUp();
                }
            }).fail(function (data) {
                console.log(data);
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
            }).fail(function (data) {
                console.log(data);
            });
        });
    }
};

$(document).ready(function () {
    app.init();
});
