// changelog

// version 1.0.15
// added #desc-{TableName}-reset action

// version 1.0.14
// remove .disableSelection from sortable elements.

// version 1.0.13
// Clear list loader
// change action button type to "button" so it won't try to submit the form.
// remove focus from time / date pickers (filters)

// version 1.0.12
// Fixed bug when the third party forms were not possible to submit from the same page.

// version 1.0.11
// Added pageNumber and pageItems params for to sortable.

// version 1.0.10
// fixed: if the table page had another form, it was not possible to submit.

// version 1.0.9
// added beforeFormSubmit() method

// version 1.0.8
// another fix for pagination filter.

// version 1.0.7
// fix for pagination filter.
// Added onListDisplay method
// #desc-{TableName}-delete action

// version 1.0.6
// Added support for "Save And Stay" button.
// Added support for .tagify elements.
// Added onActionButtonFormLoad(action, idElement) callback function for action buttons.
// Added support for list sub elements and back button

// version 1.0.5
// Fixed: tinymce was not loading on second load for modal forms.

// version 1.0.4
// added onFormDisplay callback function

// version 1.0.3
// added grouped checkbox values to _saveForm ajax requet.

// version 1.0.2
// added event to close the modal

// jQuery Plugin HiPrestaTable
// Add ajax events to PrestaShop admin tables
// version 1.0.11, Jan 22nd, 2024
// by Suren Mikaelyan

// Actions
// submitSave{FriendlyName}
// submitSave{FriendlyName}AndStay
// close{FriendlyName}Form
// close{FriendlyName}FormModal

// custom action button selector
// .hi-presta-module-action-button

// #desc-{TableName}-new
// #desc-{TableName}-back
// #desc-{TableName}-delete - delete all table items
// #desc-{TableName}-reset

// Custom button to filter the list
// .hi-module-custom-action

// Methods
// onFormDisplay()
// onActionButtonFormLoad()
// beforeFormSubmit()

// Options
// displayFormParams - object
// saveFormParams - object

(function($, window, document, undefined) {

    var pluginName = 'hiPrestaTable';
 
    function Plugin(element, options) {

        this.el = element;

        this.$el = $(element);

        this.options = $.extend({
            onFormDisplay: function() {},
            beforeFormSubmit: function() {},
            onActionButtonFormLoad: function() {},
            onListDisplay: function() {},
            displayFormParams: {},
            saveFormParams: {},
        }, $.fn[pluginName].defaults, options);

        this.init();
    }

    Plugin.prototype = {

        init: function() {
            this._tableFix();
            this._initOptions();
            this._initEvents();
            this._initSort();
            this._initPopover();
            // this._createLoader();
        },

        _tableFix: function () {
            $(document).find('.hi-presta-module-form .btn-group-action button.dropdown-toggle').attr('type', 'button');
        },

        _initOptions: function () {
            if (!this.options.tableName) {
                this.options.tableName = this.$el.attr('id').replace('form-', '');
            }

            if (!this.options.friendlyName) {
                this.options.friendlyName = this.options.tableName;
            }

            if (!this.options.formElement) {
                this.options.formElement = '#' + this.options.tableName + '_form';
            }

            this.options.tableSelector = '#' + this.$el.attr('id');
         },

        _initEvents: function() {
            var self = this;
            
            // pagination
            $(this.options.tableSelector + ' .pagination-link').off('click');
            $(this.options.tableSelector + ' .pagination-items-page').off('click');

            $(document)
                .on('click', '#desc-' + this.options.tableName + '-new', function(event) {
                    event.preventDefault();

                    self._displayForm($(this), null);
                })
                .on('click', '#desc-' + this.options.tableName + '-delete', function(event) {
                    event.preventDefault();

                    self._clearItems($(this));
                })
                .on('click', '#desc-' + this.options.tableName + '-reset', function(event) {
                    event.preventDefault();

                    self._resetList($(this), null);
                })
                .on('click', '.' + this.options.tableName + ' .edit', function(event) {
                    event.preventDefault();

                    var regexp = new RegExp(self.options.identifier + '=([0-9]+)', '');
                    var idElement = $(this).attr('href').match(regexp)[1];
                    self._displayForm($(this), idElement);
                })
                .on('click', '.' + this.options.tableName + ' .delete', function(event) {
                    event.preventDefault();

                    var regexp = new RegExp(self.options.identifier + '=([0-9]+)', '');
                    var idElement = $(this).attr('href').match(regexp)[1];
                    self._deleteItem($(this), idElement);
                })
                .on('click', '.' + this.options.tableName + ' .hi-module-custom-action', function(event) {
                    event.preventDefault();
                    var $this = $(this);
                    var params = {
                        customAction: $(this).attr('data-action'),
                        customActionId: $(this).attr('data-id')
                    }
                    self._displayList($(this), undefined, params);
                })
                .on('click', '#desc-' + this.options.tableName + '-back', function(event) {
                    event.preventDefault();
                    var $this = $(this);
                    var params = {
                        customAction: 'displayListBack',
                        customActionId: $(this).attr('href')
                    }
                    self._displayList($(this), undefined, params);
                })
                .on('click', '[name="close' + this.options.friendlyName + 'Form"]', function(event) {
                    event.preventDefault();

                    self._displayList($(this));
                })
                .on('click', '[name="close' + this.options.friendlyName + 'FormModal"]', function(event) {
                    event.preventDefault();

                    $('#hi-presta-module-modal').modal('hide');
                })
                .on('click', '.' + this.options.tableName + '-status', function(event) {
                    event.preventDefault();
    
                    let idElement = $(this).attr('data-id');
                    let currentStatus = $(this).attr('data-status');
                    let statusType = $(this).attr('data-status-type');
    
                    self._updateStatus($(this), idElement, currentStatus, statusType);
                })
                .on('click', '.' + this.options.tableName + ' .hi-module-custom-action-button', function(event) {
                    event.preventDefault();

                    var $this = $(this);
                    var params = {
                        customAction: $(this).attr('data-action'),
                        customActionId: $(this).attr('data-id')
                    }
                    self._customAction($this, params);
                })
                .on('change, keyup', '#table-' + this.options.tableName + ' .filter input', self._delay(function(e) {
                    // let's reset pagination since it'll give unexpected result when used filters after pagination
                    self.$el.find('[name="page"]').val(1);

                    let inputName;

                    // do not focus time / date pickers
                    if (!$(this).hasClass('ui-timepicker-input') && !$(this).hasClass('datepicker')) {
                        inputName = $(this).attr('name');
                    }

                    self._displayList(undefined, inputName);
                }, 300))
                .on('change', '#table-' + this.options.tableName + ' .filter select', function(event) {
                    event.preventDefault();
                    
                    // let's reset pagination since it'll give unexpected result when used filters after pagination
                    self.$el.find('[name="page"]').val(1);

                    self._displayList();
                })
                .on('click', '[name="submitSave' + this.options.friendlyName + '"]', function(event) {
                    event.preventDefault();

                    self._saveForm($(this));
                })
                .on('click', '[name="submitSave' + this.options.friendlyName + 'AndStay"]', function(event) {
                    event.preventDefault();

                    self._saveForm($(this), true);
                })
                .on('click', this.options.tableSelector + ' .hi-presta-module-action-button', function(event) {
                    event.preventDefault();

                    self._displayActionButtonForm($(this));
                })
                .on ('click', this.options.tableSelector + ' .pagination-link', function(event) {
                    event.preventDefault();

                    if (!$(this).parent().hasClass('disabled')) {
					    $('#submitFilter'+$(this).data('list-id')).val($(this).data('page'));
                        self.$el.find('[name="page"]').val($(this).data('page'));

                        self._displayList();
                    }
                })
                .on('click', this.options.tableSelector + ' .pagination-items-page', function(event) {
                    event.preventDefault();

                    $('#' + $(this).data('list-id') + '-pagination-items-page').val($(this).data('items'));

                    self._displayList();
                })
                .on('submit', self.options.formElement, function(event) {
                    // a litte info for future debuggin
                    // this was self.options.formElement instead of $(self.options.formElement)
                    // and the event was not triggering so we had to change to $(self.options.formElement)
                    // then we had to change this back to self.options.formElement because it wasn't working on blog's chatGPT page.
                    // Here is the bug in this case, when using filters and enter is used after typing in the input, it would submit the form instead of an ajax request.
                    
                    // the bug is fixed with the next line (using $(self.options.formElement) is useless)
                    event.preventDefault();

                    // don't do this
                    // self._displayList();
                })
                .on('submit', self.options.tableSelector, function(event) {
                    event.preventDefault();
                })
                .on('click', '.hi-module-list-dropdown .js-dropdown-item', function(e) {
                    e.preventDefault();

                    const $parent = $(this).closest('.hi-module-list-dropdown');
                    const dropdownType = $parent.attr('data-type');
                    const idElement = $parent.attr('data-id-element');
                    const selectedValue = $(this).attr('data-value');

                    var data = {
                        ajax: true,
                        action: 'updateListDropdown' +  self.options.friendlyName + dropdownType,
                        idElement: idElement,
                        selectedValue: selectedValue,
                        secure_key: self.options.secureKey,
                        filters: self._getFilters(),
                        pageItems: $('#' + self.options.tableName + '-pagination-items-page').val(),
                        pageNumber: self.$el.find('[name="page"]').val(),
                    };

                    data[self.options.tableName + '_pagination'] = $('#' + self.options.tableName + '-pagination-items-page').val();
                    data['submitFilter' + self.options.tableName] = self.$el.find('[name="page"]').val();

                    $.ajax({
                        type: 'POST',
                        dataType: 'JSON',
                        url: self.options.ajaxUrl,
                        data: data,
                        beforeSend: function() {
                            self._createLoader();
                        },
                        success: function(response) {
                            self._destroyLoader();

                            if (response.error) {
                                showErrorMessage(response.error);
                            } else {
                                showSuccessMessage(response.message);
                                if (response.filters) {
                                    $(self.options.tableSelector + ' tbody').replaceWith($(response.content).find('tbody'));
                                } else {
                                    $(self.options.tableSelector).replaceWith(response.content);
                                }
                                
                                self._tableFix();
                                self._initSort();
                                self._initPopover();
                            }
                        },
                        error: function(jqXHR, error, errorThrown) {
                            self._destroyLoader();
            
                            if (jqXHR.status && jqXHR.status == 400) {
                                showErrorMessage(jqXHR.responseText);
                            } else {
                                showErrorMessage(ajaxErrorMessage);
                            }
                        }
                    });
                });
        },

        _delay: function(callback, ms) {
            var timer = 0;
            return function() {
                var context = this, args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function () {
                    callback.apply(context, args);
                }, ms || 0);
            };
        },

        _displayForm: function($element, idItem) {
            var self = this;

            var data = {
                ajax: true,
                action: 'displayPostForm',
                action: 'render' + self.options.friendlyName + 'Form',
                idItem: idItem,
                secure_key: self.options.secureKey
            };

            var params = this.options.displayFormParams;

            if (typeof params == 'object' && Object.keys(params).length > 0) {
                data = Object.assign(data, params);
            }

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: self.options.ajaxUrl,
                data: data,
                beforeSend: function() {
                    if (!idItem) {
                        $element.find('i').removeClass('process-icon-new').addClass('process-icon-refresh icon-spin');
                    } else {
                        $element.find('i').removeClass('icon-pencil').addClass('icon-refresh icon-spin');
                    }
                },
                success: function(response) {
                    if (!idItem) {
                        $element.find('i').removeClass('process-icon-refresh icon-spin').addClass('process-icon-new');
                    } else {
                        $element.find('i').removeClass('icon-refresh icon-spin').addClass('icon-pencil');
                    }

                    if (response.error) {
                        showErrorMessage(response.error);
                    } else if (response.content) {
                        if (typeof response.contentType != 'undefined' && response.contentType == 'modal') {
                            // trigger tinymce re-init.
                            if (typeof tinymce != 'undefined') {
                                tinymce.remove('.autoload_rte');
                            }

                            $('#hi-presta-module-modal .content').html(response.content);
                            $('#hi-presta-module-modal').modal('show');
                        } else {
                            $(self.options.tableSelector).replaceWith(response.content);
                        }

                        $(self.options.formElement).trigger('hiPrestaTable.render' + self.options.friendlyName + 'Form');
                        self.options.onFormDisplay.call(this);
                    }
                },
                error: function(jqXHR, error, errorThrown) {
                    if (!idItem) {
                        $element.find('i').removeClass('process-icon-refresh icon-spin').addClass('process-icon-new');
                    } else {
                        $element.find('i').removeClass('icon-refresh icon-spin').addClass('icon-pencil');
                    }
    
                    if (jqXHR.status && jqXHR.status == 400) {
                        showErrorMessage(jqXHR.responseText);
                    } else {
                        showErrorMessage(ajaxErrorMessage);
                    }
                }
            });
        },

        _displayList: function($element, filterElementName, params) {
            var self = this;

            var data = {
                ajax: true,
                action: 'render' + this.options.friendlyName + 'List',
                secure_key : self.options.secureKey,
                filters: self._getFilters(),
                pageItems: $('#' + this.options.tableName + '-pagination-items-page').val(),
                pageNumber: self.$el.find('[name="page"]').val(),
            }

            data[this.options.tableName + '_pagination'] = $('#' + this.options.tableName + '-pagination-items-page').val();
            data['submitFilter' + this.options.tableName] = self.$el.find('[name="page"]').val();

            if (typeof params == 'object') {
                data = Object.assign(data, params);
            }

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: self.options.ajaxUrl,
                data: data,
                beforeSend: function() {
                    if (typeof $element != 'undefined') {
                        $element.addClass('hi-presta-module-spinner');
                    } else {
                        self._createLoader();
                    }
                },
                success: function(response) {
                    self._destroyLoader();
                    if (typeof $element != 'undefined') {
                        $element.removeClass('hi-presta-module-spinner');
                    }
                    
                    if (response.error) {
                        showErrorMessage(response.error);
                    } else if (response.content) {
                        if (typeof tinymce != 'undefined') {
                            tinymce.remove('.autoload_rte');
                        }

                        if ($(self.options.formElement).length && !$('#hi-presta-module-modal').find(self.options.formElement).length) {
                            $(self.options.formElement).replaceWith(response.content);
                        } else {
                            if (response.filters) {
                                $(self.options.tableSelector + ' tbody').replaceWith($(response.content).find('tbody'));
                                $(self.options.tableSelector + ' .table-responsive-row').next().replaceWith($(response.content).find('.table-responsive-row').next());
                            } else {
                                $(self.options.tableSelector).replaceWith(response.content);
                            }
                        }

                        if (typeof filterElementName != undefined) {
                            $('input[name="' + filterElementName + '"]').focus();
                        }

                        self.options.onListDisplay.call(this);
                    }

                    self._tableFix();
                    self._initSort();
                    self._initPopover();
                },
                error: function(jqXHR, error, errorThrown) {
                    self._destroyLoader();
                    if (typeof $element != 'undefined') {
                        $element.removeClass('hi-presta-module-spinner');
                    }
    
                    if (jqXHR.status && jqXHR.status == 400) {
                        showErrorMessage(jqXHR.responseText);
                    } else {
                        showErrorMessage(ajaxErrorMessage);
                    }
                }
            });
        },

        _getFilters: function() {
            var filters = {};
            $('#table-' + this.options.tableName + ' .filter input, #table-' + this.options.tableName + ' .filter select').each(function() {
                if ($(this).val() != '') {
                    filters[$(this).attr('name')] = $(this).val();
                }
            });

            return filters;
        },

        _deleteItem: function($element, idElement) {
            var self = this;
            var data = {
                ajax: true,
                action: 'delete' + this.options.friendlyName,
                idElement: idElement,
                secure_key: this.options.secureKey,
                filters: self._getFilters(),
                pageItems: $('#' + this.options.tableName + '-pagination-items-page').val(),
                pageNumber: self.$el.find('[name="page"]').val(),
            };

            data[this.options.tableName + '_pagination'] = $('#' + this.options.tableName + '-pagination-items-page').val();
            data['submitFilter' + this.options.tableName] = self.$el.find('[name="page"]').val();

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: this.options.ajaxUrl,
                data: data,
                beforeSend: function() {
                    if (typeof $element != 'undefined') {
                        $element.find('i.icon-trash').removeClass('icon-trash').addClass('icon-refresh icon-spin');
                    }
                },
                success: function(response) {
                    if (typeof $element != 'undefined') {
                        $element.find('i.icon-refresh').removeClass('icon-refresh icon-spin').addClass('icon-trash');
                    }
    
                    if (response.error) {
                        showErrorMessage(response.error);
                    } else {
                        showSuccessMessage(response.message);
                        if (response.filters) {
                            $(self.options.tableSelector + ' tbody').replaceWith($(response.content).find('tbody'));
                        } else {
                            $(self.options.tableSelector).replaceWith(response.content);
                        }
                        
                        self._tableFix();
                        self._initSort();
                        self._initPopover();
                    }
                },
                error: function(jqXHR, error, errorThrown) {
                    if (typeof $element != 'undefined') {
                        $element.find('i.icon-refresh').removeClass('icon-refresh icon-spin').addClass('icon-trash');
                    }
    
                    if (jqXHR.status && jqXHR.status == 400) {
                        showErrorMessage(jqXHR.responseText);
                    } else {
                        showErrorMessage(ajaxErrorMessage);
                    }
                }
            });
        },

        _clearItems: function($element) {
            var self = this;
            var data = {
                ajax: true,
                action: 'clear' + this.options.friendlyName,
                secure_key: this.options.secureKey,
            };

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: this.options.ajaxUrl,
                data: data,
                beforeSend: function() {
                    if (typeof $element != 'undefined') {
                        $element.find('i.process-icon-delete').removeClass('icon-trash').addClass('icon-refresh icon-spin');
                    }
                },
                success: function(response) {
                    if (typeof $element != 'undefined') {
                        $element.find('i.icon-refresh').removeClass('icon-refresh icon-spin').addClass('process-icon-delete');
                    }
    
                    if (response.error) {
                        showErrorMessage(response.error);
                    } else {
                        showSuccessMessage(response.message);
                        if (response.filters) {
                            $(self.options.tableSelector + ' tbody').replaceWith($(response.content).find('tbody'));
                        } else {
                            $(self.options.tableSelector).replaceWith(response.content);
                        }
                        
                        self._tableFix();
                        self._initSort();
                        self._initPopover();
                    }
                },
                error: function(jqXHR, error, errorThrown) {
                    if (typeof $element != 'undefined') {
                        $element.find('i.icon-refresh').removeClass('icon-refresh icon-spin').addClass('process-icon-delete');
                    }
    
                    if (jqXHR.status && jqXHR.status == 400) {
                        showErrorMessage(jqXHR.responseText);
                    } else {
                        showErrorMessage(ajaxErrorMessage);
                    }
                }
            });
        },

        _initSort: function () {
            self = this;
            if ($('#table-' + this.options.tableName).length) {
                var $table = $('#table-' + this.options.tableName);
            } else {
                var $table = $('.table.' + this.options.tableName);
            }

            if (!$table.find('.icon-move').length) {
                return false;
            }

            $table.find('tbody').sortable({
                handle: '.icon-move',
                stop: function(event, ui) {
                    var sortedItems = [];
                    $table.find('tbody tr').each(function(index) {
                        var idItem = parseInt($(this).find('td:eq(1)').text());
                        sortedItems.push('sortedItems[' + (index + 1) + ']=' + idItem);
                    });
        
                    var params = sortedItems.join('&');
                    params += '&ajax=1&secure_key=' + self.options.secureKey + '&action=sort' + self.options.friendlyName + 'Elements'
                    params += '&pageNumber=' + self.$el.find('[name="page"]').val();
                    params += '&pageItems=' + $('#' + self.options.tableName + '-pagination-items-page').val();
                    $.ajax({
                        type: 'POST',
                        dataType: 'JSON',
                        url: self.options.ajaxUrl,
                        async: true,
                        data: params,
                        success: function(response) {
                            if (response.error) {
                                showErrorMessage(response.error);
                            } else {
                                showSuccessMessage(response.message);
                            }
                        }
                    });
                }
            });
        },

        _initPopover: function() {
            $(self.options.tableSelector + ' [data-toggle="popover"]').popover();
        },
        
        _updateStatus: function($element, idElement, currentStatus, statusType) {
            var self = this;
            if (typeof statusType == 'undefined' || !statusType) {
                statusType = 'active';
            }

            var data = {
                ajax: true,
                action: 'update' + self.options.friendlyName + 'Status',
                idElement: idElement,
                currentStatus: currentStatus,
                statusType: statusType,
                secure_key: self.options.secureKey,
                filters: self._getFilters(),
                pageItems: $('#' + this.options.tableName + '-pagination-items-page').val(),
                pageNumber: self.$el.find('[name="page"]').val(),
            }
            data[this.options.tableName + '_pagination'] = $('#' + this.options.tableName + '-pagination-items-page').val();
            data['submitFilter' + this.options.tableName] = self.$el.find('[name="page"]').val();

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: this.options.ajaxUrl,
                data: data,
                beforeSend: function() {
                    if ($element.hasClass('btn-success')) {
                        $element.find('i').removeClass('icon-check').addClass('icon-refresh icon-spin');
                    } else {
                        $element.find('i').removeClass('icon-remove').addClass('icon-refresh icon-spin');
                    }
                },
                success: function(response) {
                    if ($element.hasClass('btn-success')) {
                        $element.find('i').removeClass('icon-refresh icon-spin').addClass('icon-check');
                    } else {
                        $element.find('i').removeClass('icon-refresh icon-spin').addClass('icon-remove');
                    }
    
                    if (response.error) {
                        showErrorMessage(response.error);
                    } else {
                        showSuccessMessage(response.message);
                        if (response.filters) {
                            $(self.options.tableSelector + ' tbody').replaceWith($(response.content).find('tbody'));
                        } else {
                            $(self.options.tableSelector).replaceWith(response.content);
                        }
                        self._tableFix();
                        self._initSort();
                        self._initPopover();
                    }
                },
                error: function(jqXHR, error, errorThrown) {
                    if ($element.hasClass('btn-success')) {
                        $element.find('i').removeClass('icon-refresh icon-spin').addClass('icon-check');
                    } else {
                        $element.find('i').removeClass('icon-refresh icon-spin').addClass('icon-remove');
                    }
    
                    if (jqXHR.status && jqXHR.status == 400) {
                        showErrorMessage(jqXHR.responseText);
                    } else {
                        showErrorMessage(ajaxErrorMessage);
                    }
                }
            });
        },
        
        _customAction: function($element, params) {
            var self = this;

            var data = {
                ajax: true,
                action: self.options.friendlyName + params.customAction,
                idElement: params.customActionId,
                secure_key: self.options.secureKey,
                filters: self._getFilters(),
                pageItems: $('#' + this.options.tableName + '-pagination-items-page').val(),
                pageNumber: self.$el.find('[name="page"]').val(),
            }
            data[this.options.tableName + '_pagination'] = $('#' + this.options.tableName + '-pagination-items-page').val();
            data['submitFilter' + this.options.tableName] = self.$el.find('[name="page"]').val();

            var icon = $element.find('i').attr('class');

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: this.options.ajaxUrl,
                data: data,
                beforeSend: function() {
                    if (typeof icon != 'undefined') {
                        $element.find('i').removeClass(icon).addClass('icon-refresh icon-spin');
                    }
                },
                success: function(response) {
                    if (typeof icon != 'undefined') {
                        $element.find('i').removeClass('icon-refresh icon-spin').addClass(icon);
                    }
    
                    if (response.error) {
                        showErrorMessage(response.error);
                    } else {
                        showSuccessMessage(response.message);
                        if (response.filters) {
                            $(self.options.tableSelector + ' tbody').replaceWith($(response.content).find('tbody'));
                        } else {
                            $(self.options.tableSelector).replaceWith(response.content);
                        }
                        self._tableFix();
                        self._initSort();
                        self._initPopover();
                    }
                },
                error: function(jqXHR, error, errorThrown) {
                    if (typeof icon != 'undefined') {
                        $element.find('i').removeClass('icon-refresh icon-spin').addClass(icon);
                    }
    
                    if (jqXHR.status && jqXHR.status == 400) {
                        showErrorMessage(jqXHR.responseText);
                    } else {
                        showErrorMessage(ajaxErrorMessage);
                    }
                }
            });
        },

        _saveForm: function($element, stayAfterSave = false) {
            var self = this;

            // perform an action before form submit
            self.options.beforeFormSubmit.call(this);

            if (typeof tinymce != 'undefined') {
                tinymce.triggerSave();
            }

            var form = $element.closest('form');

            form.find('.tagify').each(function(){
                var inputId = $(this).attr('id');
                $(this).find('#' + inputId).val($('#' + inputId).tagify('serialize'));
            });

            var formdata = new FormData($(form)[0])
            formdata.append('action', 'save' + self.options.friendlyName);
            formdata.append('secure_key', self.options.secureKey);
            formdata.append('ajax', true);
            formdata.append('stayAfterSave', stayAfterSave);

            // We need to add filters in case the form is saved from modal.
            let filters = self._getFilters();
            for ( var key in filters ) {
                formdata.append('filters['+key+']', filters[key]);
            }

            // add grouped checkboxes
            form.find('.hi-presta-checkbox-group').each(function() {
                let dataName = $(this).find('input[type="checkbox"]').attr('name').split('_')[0];
                let checkedValues = $(this).find('input:checkbox:checked').map(function() {
                        return this.value;
                }).get().join(',');

                formdata.append(dataName, checkedValues);
            });

            // add shops for multishop configuration
            if (form.find('#shop-tree').length > 0) {
                let shops = form.find('#shop-tree .tree-item-name input:checkbox:checked').map(function() {
                    return this.value;
                }).get().join(',');

                formdata.append('shops', shops);
            }

            // add custom params if needed
            var params = this.options.saveFormParams;
            if (typeof params == 'object' && Object.keys(params).length > 0) {
                for (const paramKey in params) {
                    formdata.delete(paramKey);
                    formdata.append(paramKey, params[paramKey]);
                }
            }

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: self.options.ajaxUrl,
                data: formdata,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $element.addClass('hi-presta-module-spinner');
                },
                success: function(response) {
                    $element.removeClass('hi-presta-module-spinner');

                    if (response.error) {
                        showErrorMessage(response.error);
                    } else {
                        if (typeof tinymce != 'undefined') {
                            tinymce.remove('.autoload_rte');
                        }
                        showSuccessMessage(response.message);
                        
                        if (stayAfterSave) {
                            $(self.options.formElement).replaceWith(response.content);
                            self.options.onFormDisplay.call(this);
                        } else {
                            if (typeof response.contentType != 'undefined' && response.contentType == 'modal') {
                                $('#hi-presta-module-modal').modal('hide');
    
                                if (response.filters) {
                                    $(self.options.tableSelector + ' tbody').replaceWith($(response.content).find('tbody'));
                                    $(self.options.tableSelector + ' .table-responsive-row').next().replaceWith($(response.content).find('.table-responsive-row').next());
                                } else {
                                    $(self.options.tableSelector).replaceWith(response.content);
                                }
                            } else {
                                $(self.options.formElement).replaceWith(response.content);
                            }

                            self.options.onListDisplay.call(this);
                            self._tableFix();
                            self._initSort();
                            self._initPopover();
                        }
                    }
                },
                error: function(jqXHR, error, errorThrown) {
                    $element.removeClass('hi-presta-module-spinner');
    
                    if (jqXHR.status && jqXHR.status == 400) {
                        showErrorMessage(jqXHR.responseText);
                    } else {
                        showErrorMessage(ajaxErrorMessage);
                    }
                }
            });
        },

        _resetList: function($element) {
            var data = {
                ajax: true,
                action: 'reset' + self.options.friendlyName + 'List',
                secure_key: self.options.secureKey,
                pageItems: $('#' + this.options.tableName + '-pagination-items-page').val(),
                pageNumber: self.$el.find('[name="page"]').val(),
            }

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: self.options.ajaxUrl,
                data: data,
                beforeSend: function() {
                    $element.find('i').removeClass('process-icon-reset').addClass('process-icon-refresh icon-spin');
                },
                success: function(response) {
                    $element.find('i').removeClass('process-icon-refresh icon-spin').addClass('process-icon-reset');

                    if (response.error) {
                        showErrorMessage(response.error);
                    } else if (response.content) {
                        showSuccessMessage(response.message);
                        $(self.options.tableSelector).replaceWith(response.content);

                        self._initSort();
                        self._initPopover();
                    }
                },
                error: function(jqXHR, error, errorThrown) {
                    $element.find('i').removeClass('process-icon-refresh icon-spin').addClass('process-icon-reset');
    
                    if (jqXHR.status && jqXHR.status == 400) {
                        showErrorMessage(jqXHR.responseText);
                    } else {
                        showErrorMessage(ajaxErrorMessage);
                    }
                }
            });
        },

        _displayActionButtonForm: function($element) {
            var self = this;
            var icon = $element.find('i').attr('class');

            let action = $element.attr('data-action-type');
            let idElement = $element.attr('data-id-element');
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: self.options.ajaxUrl,
                data:{
                    ajax: true,
                    action: action,
                    idElement: idElement,
                    secure_key: self.options.secureKey,
                },
                beforeSend: function() {
                    if (icon) {
                        $element.find('i').removeClass(icon).addClass('icon-refresh icon-spin');
                    }
                },
                success: function(response) {
                    if (icon) {
                        $element.find('i').removeClass('icon-refresh icon-spin').addClass(icon);
                    }

                    $("#hi-presta-module-modal .content").html(response.content);
                    $('#hi-presta-module-modal').modal('show');

                    $(self.options.tableSelector).trigger('hiPrestaTable.' + action);
                    self.options.onActionButtonFormLoad.call(this, action, idElement);
                },
                error: function(jqXHR, error, errorThrown) {
                    if (icon) {
                        $element.find('i').removeClass('icon-refresh icon-spin').addClass(icon);
                    }
    
                    if (jqXHR.status && jqXHR.status == 400) {
                        showErrorMessage(jqXHR.responseText);
                    } else {
                        showErrorMessage(ajaxErrorMessage);
                    }
                }
            });
        },

        _createLoader: function() {
            let $loader = '<tr class="hi-presta-table-loader"><td><i class="icon-refresh icon-spin"></i></td></tr>';
            $(self.options.tableSelector + ' tbody').css({'position': 'relative'});
            $(self.options.tableSelector + ' tbody').prepend($loader);

        },

        _destroyLoader: function() {
            $('.hi-presta-table-loader').remove();
            $(self.options.tableSelector + ' tbody').css('position', 'static');
        },

        displayList: function($element) {
            this._displayList($element);
        },
    };

    $.fn[pluginName] = function(options) {
        var args = arguments;

        if (options === undefined || typeof options === 'object') {
            return this.each(function() {
                if (!$.data(this, 'plugin_' + pluginName)) {
                    $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
                }
            });
        } else if (typeof options === 'string' && options[0] !== '_' && options !== 'init') {
            if (Array.prototype.slice.call(args, 1).length == 0 && $.inArray(options, $.fn[pluginName].getters) != -1) {
                var instance = $.data(this[0], 'plugin_' + pluginName);
                return instance[options].apply(instance, Array.prototype.slice.call(args, 1));
            } else {
                return this.each(function() {
                    var instance = $.data(this, 'plugin_' + pluginName);
                    if (instance instanceof Plugin && typeof instance[options] === 'function') {
                        instance[options].apply(instance, Array.prototype.slice.call(args, 1));
                    }
                });
            }
        }
    };

    /**
     * Default options
     */
    $.fn[pluginName].defaults = {
        tableName: null,
        friendlyName: null,
        formElement: null,
        secureKey: null,
        ajaxUrl: null,
        identifier: null
    };

})(jQuery, window, document);