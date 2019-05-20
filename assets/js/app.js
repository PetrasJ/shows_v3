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
    },
    initTooltip: function () {
        $('[data-toggle="tooltip"]').tooltip();
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
        $('.show-offset').unbind().change(function () {
            const id = $(this).data('id');
            const value = $(this).val();
            $.ajax({
                type: 'post',
                url: window.baseUrl + 'shows/update',
                data: {
                    id: id,
                    value: $(this).val()
                },
                success: function () {
                    $('#' + id).find('.show-settings').data('offset', value)
                }
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
                    window.location.hash = $(this).data('id');
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
                url: window.baseUrl + 'watch',
                data: {
                    id: id,
                },
                success: () => {
                    if ($('.unwatched-shows').length) {
                        const showCount = show.find('.count');
                        const count = parseInt(showCount.html()) - 1;
                        showCount.html(count);
                        if (count === 0) {
                            show.hide();
                            $('.unwatched-shows').slideDown();
                        }
                        episode.slideUp();
                    } else {
                        episode.find('.unwatch-episode').removeClass('d-none');
                        $(this).hide();
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
                    comment: $(this).find('.comment').val()
                },
            }).fail(function (data) {
                console.log(data);
            }).always(function () {
                loaded();
            });
        });
        this.initTooltip();
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
    },
    initCalendar: function () {
        if ($('.calendar').length !== 0) {
            const monthPicker = $('.month-picker');
            console.log(window.location.hash);
            this.loadCalendar(monthPicker.val())
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
    loadCalendar: function (id) {
        $.ajax({
            type: 'get',
            url: window.baseUrl + 'calendar/month/' + id,
            success: (data) => {
                $('.calendar').html(data)
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
};

function loaded() {
    $('.overlay').css('opacity', 0);
    setTimeout(function () {
        $('.overlay').hide()
    }, 300);
};

$(document).ready(function () {
    app.init();
});
