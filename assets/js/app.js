require('../css/app.css');
const $ = require('jquery');
window.$ = $;
require('bootstrap');

require('jquery-ui/ui/widgets/autocomplete');
require('bootstrap-select');

const app = {
    init: function () {
        this.initSearch();
        this.initModals();
        this.initUnwatchedEpisodes();
        $('.selectpicker').selectpicker();
        this.initAddRemoveShow();
        this.initShowList();
        this.initConfirm();
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
                window.location.href = window.baseUrl + 'search/results/' + ui.item.value;
            }
        });
    },
    initAddRemoveShow: function () {
        $('.add-show').unbind().on('click', function () {
            loading();
            $.ajax({
                type: 'post',
                url: window.baseUrl + 'shows/add/' + $(this).data('id'),
                data: {
                    id: $(this).data('id'),
                    value: $(this).val()
                },
                success: () => {
                    $(this).hide()
                }
            }).fail(function (data) {
                console.log(data);
            }).always(function () {
                loaded();
            });
        });

        $('.remove-show').unbind().on('click', function () {
            loading();
            $.ajax({
                type: 'post',
                url: window.baseUrl + 'shows/remove/' + $(this).data('id'),
                data: {
                    id: $(this).data('id'),
                    value: $(this).val()
                },
                success: () => {
                    $(this).hide()
                }
            }).fail(function (data) {
                console.log(data);
            }).always(function () {
                loaded();
            });
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
    initShowList: function () {
        $('.update-show').unbind().on('click', function () {
            loading();
            const id = $(this).data('id');
            $.ajax({
                type: 'post',
                url: window.baseUrl + 'shows/' + $(this).data('action') + '/' + id,
                success: () => {
                    $('#' + id).slideUp()
                }
            }).fail(function (data) {
                console.log(data);
            }).always(function () {
                loaded();
            });
        });
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
            loading();
            $.ajax({
                type: 'get',
                url: $(this).data('link'),
                success: (data) => {
                    $('#result').hide().html(data).slideDown();
                    t.initWatchActions();
                    $('.unwatched-shows').slideUp();
                }
            }).fail(function (data) {
                console.log(data);
            }).always(function () {
                loaded();
            });

        })
    },
    initWatchActions: function () {
        $('.watch-episode').unbind().on('click', function () {
            loading();
            const id = $(this).data('id');
            const episode = $('#' + id);
            const show = $('#show_' + episode.data('show-id'));
            $.ajax({
                type: 'post',
                url: window.baseUrl + 'unwatched/watch',
                data: {
                    id: id,
                },
                success: () => {
                    const showCount = show.find('.count');
                    const count = parseInt(showCount.html()) - 1;
                    showCount.html(count);
                    if (count === 0) {
                        show.hide();
                        $('.unwatched-shows').slideDown();
                    }
                    episode.slideUp();
                }
            }).fail(function (data) {
                console.log(data);
            }).always(function() {
                loaded();
            });
        });

        $('.comment-episode').unbind().submit(function (e) {
            loading();
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
            }).always(function () {
                loaded();
            });
        });
    },
    initConfirm: function () {
        $('#confirm').on('show.bs.modal', function (e) {
            const button = $(e.relatedTarget);
            const modal = $(this);
            modal.find('.modal-body').html(button.data('text'));
            $(this).find('.confirm').unbind().on('click', function () {
                loading();
                $.ajax({
                    type: 'post',
                    url: button.data('action'),
                    success: () => {
                        $('#' + button.data('id')).slideUp()
                    }
                }).fail(function (data) {
                    console.log(data);
                }).always(function () {
                    modal.modal('hide');
                    loaded();
                });
            });
        });
    }
};

function loading() {
    $('.overlay').show().css('opacity', 1);
};

function loaded() {
    $('.overlay').css('opacity', 0);
    setTimeout(function(){ $('.overlay').hide() }, 300);
};

$(document).ready(function () {
    app.init();
});
