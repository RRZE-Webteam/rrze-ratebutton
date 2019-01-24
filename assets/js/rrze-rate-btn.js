(function($, window, document, undefined) {
    "use strict";

    var pluginName = "RRZERateButton",
        $window = $(window),
        $document = $(document),
        defaults = {
            ID: 0,
            nonce: "",
            rateStatus: 0,
            counterSelector: ".count-box",
            containerSelector: ".rrze-rate-container",
            buttonSelector: ".rrze-rate-btn"
        },
        attrMap = {
            "rrze-rate-id": "ID",
            "rrze-rate-nonce": "nonce",
            "rrze-rate-status": "rateStatus"
        };

    function Plugin(element, options) {
        this.element = element;
        this.$element = $(element);
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;

        this.buttonElement = this.$element.find(this.settings.buttonSelector);
        this.containerElement = this.$element.find(this.settings.containerSelector);
        this.counterElement = this.containerElement.find(
            this.settings.counterSelector
        );

        for (var attrName in attrMap) {
            var value = this.buttonElement.data(attrName);
            if (value !== undefined) {
                this.settings[attrMap[attrName]] = value;
            }
        }
        this.init();
    }

    $.extend(Plugin.prototype, {
        init: function() {
            this.buttonElement.click(this.initRate.bind(this));
        },

        ajax: function(args, callback) {
            $.ajax({
                url: rrze_rate_params.ajax_url,
                type: "POST",
                cache: false,
                dataType: "json",
                data: args
            }).done(callback);
        },

        initRate: function(event) {
            event.stopPropagation();

            this.updateAllButtons();

            this.buttonElement.prop("disabled", true);

            this.containerElement.addClass("rrze-rate-is-loading");

            this.ajax({
                    action: "rrze_rate_process",
                    id: this.settings.ID,
                    nonce: this.settings.nonce,
                    status: this.settings.rateStatus
                },
                function(response) {
                    this.containerElement.removeClass("rrze-rate-is-loading");

                    if (response.success) {
                        this.updateMarkup(response);
                    }

                    this.buttonElement.prop("disabled", false);
                }.bind(this)
            );
        },

        updateMarkup: function(response) {
            switch (this.settings.rateStatus) {
                case 1:
                    this.buttonElement.attr("data-rrze-rate-status", 2);
                    this.settings.rateStatus = 2;
                    this.containerElement
                        .addClass("rrze-rate-is-rated")
                        .removeClass("rrze-rate-is-not-rated");
                    this.containerElement
                        .children()
                        .first()
                        .addClass("rrze-rate-click-is-disabled");
                    this.counterElement.text(response.data.content);
                    break;
                case 2:
                    this.containerElement
                        .children()
                        .first()
                        .addClass("rrze-rate-click-is-disabled");
                    break;
            }

        },

        updateAllButtons: function() {
            this.allButtons = $document.find(
                ".rrze-rate-btn-" + this.settings.ID
            );
            if (this.allButtons.length > 1) {
                this.buttonElement = this.allButtons;
                this.containerElement = this.buttonElement.closest(
                    this.settings.containerSelector
                );
                this.counterElement = this.containerElement.find(
                    this.settings.counterSelector
                );
            }
        },

    });

    $.fn[pluginName] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
    };
})(jQuery, window, document);

(function($) {
    $(function() {
        $(this).bind("DOMNodeInserted", function(e) {
            $(".rrze-rate-wrap").RRZERateButton();
        });
    });

    $(".rrze-rate-wrap").RRZERateButton();
})(jQuery);
