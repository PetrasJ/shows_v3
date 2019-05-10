require('../css/app.css');
const $ = require('jquery');
window.$ = $;

require('jquery-ui/ui/widgets/autocomplete');

const app = {
  init: function() {
    this.initSearch();
  },
  initSearch: function() {
      $('#search_show_search').autocomplete({
          source: '/search',
          minLength: 2,
          delay: 100,
          focus: function (event) {
              event.preventDefault();
          },
          select: function (event, ui) {
              alert(ui.item.value);
          }
      });
  }

};

$(document).ready(function () {
    app.init();
});
