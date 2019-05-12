require('../css/app.css');
const $ = require('jquery');
window.$ = $;
require('bootstrap');

require('jquery-ui/ui/widgets/autocomplete');

const app = {
    init: function () {
        this.initSearch();
        this.initModals();
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
        $('#show-settings').on('shown.bs.modal', function (e) {
            const button = $(e.relatedTarget);
            const modal = $(this);
            modal.find('.modal-title').html(button.data('title'));
            modal.find('.modal-body').html(button.data('id'));
        })
    }

};

$(document).ready(function () {
    app.init();
});
