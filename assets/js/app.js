require('../css/app.css');
const $ = require('jquery');
window.$ = $;
require('bootstrap');

require('jquery-ui/ui/widgets/autocomplete');
require('bootstrap-select');
require('bootstrap-datepicker');
require('bootstrap-datepicker/dist/locales/bootstrap-datepicker.ru.min');
require('bootstrap-datepicker/dist/locales/bootstrap-datepicker.lt.min');

const app = {
    init: function () {
        this.initSearch();
        this.initModals();
        this.initUnwatchedEpisodes();
        this.initWatchActions();
        $('.selectpicker').selectpicker();
        this.initAddRemoveShow();
        this.initShowList();
        this.initConfirm();
        this.initCalendar();
        this.initTooltip();
        this.initBackToTop();
    },
    initBackToTop: function () {
        const backToTop = $('#back-to-top');
        $(window).scroll(function () {
            $(window).scrollTop() > 200 ? backToTop.fadeIn(500) : backToTop.fadeOut(500);
        });
        $(window).scrollTop() > 200 ? backToTop.fadeIn(500) : backToTop.fadeOut(500);

        backToTop.unbind().click(function () {
            return $('html, body').animate({
                scrollTop: 0
            }, "800");
        });
    },
    initTooltip: function () {
        $('[title]').tooltip();
    },
    initSearch: function () {
        const form = $('form#search');
        form.find('.term').autocomplete({
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
        form.on('submit', function (e) {
            e.preventDefault();
            window.location.href = window.baseUrl + 'search/results/' + form.find('.term').val();
        })
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
                url: window.baseUrl + 'shows/remove/' + $(this).data('user-show-id'),
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
            modal.find('.show-offset').val(button.data('offset')).attr('data-user-show-id', button.data('user-show-id'));
            t.initSettings();
        })
    },
    initShowList: function () {
        $('.update-show').unbind().on('click', function () {
            loading();
            const id = $(this).data('user-show-id');
            $.ajax({
                type: 'post',
                url: window.baseUrl + 'shows/' + $(this).data('action') + '/' + id,
                success: () => {
                    $('#' + id).slideUp('fast')
                }
            }).fail(function (data) {
                console.log(data);
            }).always(function () {
                loaded();
            });
        });
    },
    initSettings: function () {
        $('.show-offset').unbind().change(function () {
            const userShowId = $(this).data('user-show-id');
            const value = $(this).val();
            $.ajax({
                type: 'post',
                url: window.baseUrl + 'shows/update',
                data: {
                    userShowId: userShowId,
                    value: $(this).val()
                },
                success: function () {
                    $('#' + userShowId).find('.show-settings').data('offset', value)
                }
            }).fail(function (data) {
                console.log(data);
            });

        })
    },
    initUnwatchedEpisodes: function () {
        const t = this;
        $('.unwatched-show').unbind().on('click', function () {
            t.loadUnwatchedEpisodes($(this).data('id'), true);
        });

        if ($('.unwatched-shows').length > 0) {
            const showId = window.location.hash.substr(1);
            if (showId) {
                t.loadUnwatchedEpisodes(showId, false);
            }
        }
    },
    loadUnwatchedEpisodes: function (showId, hideShows) {
        loading();
        $.ajax({
            type: 'get',
            url: window.baseUrl + 'episodes/' + showId,
            success: (data) => {
                $('#result').hide().html(data).slideDown('fast');
                this.initWatchActions();
                if (hideShows) {
                    $('.unwatched-shows').slideUp('fast');
                }
                window.location.hash = showId;
            }
        }).fail((data) => {
            console.log(data);
        }).always(() => {
            loaded();
        });
    },
    initWatchActions: function () {
        $('.watch-episode').unbind().on('click', function () {
            loading();
            const id = $(this).data('id');
            const episode = $('#' + id);
            const show = $('#show_' + episode.data('show-id'));
            $.ajax({
                type: 'post',
                url: window.baseUrl + 'watch',
                data: {
                    id: id,
                    userShowId: $(this).data('user-show-id')
                },
                success: () => {
                    const unwatchedShwos = $('.unwatched-shows');
                    if (unwatchedShwos.length) {
                        const showCount = show.find('.count');
                        const count = parseInt(showCount.html()) - 1;
                        showCount.html(count);
                        if (count === 0) {
                            show.hide();
                            unwatchedShwos.slideDown('fast');
                            $('#result').html('');
                        }
                        episode.slideUp('fast');
                    } else {
                        episode.find('.unwatch-episode').removeClass('d-none');
                        $(this).addClass('d-none');
                    }
                }
            }).fail(function (data) {
                console.log(data);
            }).always(function () {
                loaded();
            });
        });
        $('.comment-episode').unbind().submit(function (e) {
            loading();
            e.preventDefault();
            $.ajax({
                type: 'post',
                url: window.baseUrl + 'comment',
                data: {
                    id: $(this).data('id'),
                    comment: $(this).find('.comment').val(),
                    userShowId: $(this).data('user-show-id')
                },
            }).fail(function (data) {
                console.log(data);
            }).always(function () {
                loaded();
            });
        });
        $('.unwatch-episode').unbind().on('click', function () {
            loading();
            const id = $(this).data('id');
            const episode = $('#' + id);

            $.ajax({
                type: 'post',
                url: window.baseUrl + 'shows/unwatch',
                data: {
                    id: id,
                    userShowId: $(this).data('user-show-id')
                },
                success: () => {
                    episode.find('.watch-episode').removeClass('d-none');
                    $(this).addClass('d-none');
                }
            }).fail(function (data) {
                console.log(data);
            }).always(function () {
                loaded();
            });
        });
        this.initTooltip();
    },
    initConfirm: function () {
        $('#confirm').unbind().on('show.bs.modal', function (e) {
            const button = $(e.relatedTarget);
            const modal = $(this);
            modal.find('.modal-body').html(button.data('text'));
            $(this).find('.confirm').unbind().on('click', function () {
                loading();
                $.ajax({
                    type: 'post',
                    url: button.data('action'),
                    success: () => {
                        if (button.data('remove')) {
                            $('#' + button.data('user-show-id')).slideUp('fast')
                        }
                    }
                }).fail(function (data) {
                    console.log(data);
                }).always(function () {
                    modal.modal('hide');
                    loaded();
                });
            });
        });
    },
    initCalendar: function () {
        if ($('.calendar').length !== 0) {
            const month = window.location.hash.substr(1);
            const monthPicker = $('.month-picker');
            if (month) {
                monthPicker.val(month);
            }
            this.loadCalendar(monthPicker.val());
            const t = this;
            monthPicker.datepicker({
                format: "yyyy-mm",
                startView: "months",
                minViewMode: "months",
                language: $('html').attr('lang'),
                autoclose: true
            }).on('changeMonth', function () {
                loading();
                const el = $(this);
                setTimeout(function () {
                    t.loadCalendar(el.val());
                    window.location.hash = el.val();
                }, 10);
            });
        }
    },
    initCalendarHideActions: function () {
        const buttons = $('.calendar-show');
        buttons.attr('data-show', true).css('opacity', '1');
        buttons.unbind().on('click', function () {
            const element = $(this);
            if (element.attr('data-show') === "true") {
                $(element.data('class')).slideUp('fast');
                element.attr('data-show', false).css('opacity', '0.7');
            } else {
                $(element.data('class')).slideDown('fast');
                element.attr('data-show', true).css('opacity', '1');
            }
        })
    },
    loadCalendar: function (id) {
        $.ajax({
            type: 'get',
            url: window.baseUrl + 'calendar/month/' + id,
            success: (data) => {
                $('.calendar').html(data);
                this.initTooltip();
                this.initCalendarHideActions();
            }
        }).fail(function (data) {
            console.log(data);
        }).always(function () {
            loaded();
        });
    }
};

function loading() {
    $('.overlay').show().css('opacity', 1);
}

function loaded() {
    $('.overlay').css('opacity', 0);
    setTimeout(function () {
        $('.overlay').hide()
    }, 300);
}

$(document).ready(function () {
    app.init();
});
