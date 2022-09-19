define(function() {
    'use strict';

    return {
        el: '#grid-frontend-customer-customer-address-grid-1907571008',
        gridName: 'frontend-customer-customer-address-grid',
        builders: [
            'orofilter\/js\/datafilter-builder',
            'orodatagrid\/js\/totals-builder'
        ],
        metadata: {
            jsmodules: [
                'orofilter\/js\/datafilter-builder',
                'orodatagrid\/js\/totals-builder'
            ],
            options: {
                gridName: 'frontend-customer-customer-address-grid',
                frontend: true,
                additional_fields: [],
                inputName: false,
                toolbarOptions: {
                    placement: {
                        bottom: true,
                        top: true
                    },
                    hide: false,
                    addResetAction: true,
                    addRefreshAction: true,
                    datagridSettingsManager: true,
                    turnOffToolbarRecordsNumber: 0,
                    pageSize: {
                    },
                    pagination: {
                        hide: false,
                        onePage: false
                    },
                    addSorting: false,
                    disableNotSelectedOption: false
                },
                urlParams: {
                    originalRoute: 'oro_customer_frontend_customer_user_address_index'
                },
                route: 'oro_frontend_datagrid_index',
                contentTags: [
                    'Oro_Bundle_CustomerBundle_Entity_CustomerAddress_type_collection'
                ],
                multipleSorting: false,
                url: ''
            },
            lazy: true,
            massActions: {
                'delete': {
                    label: 'Delete',
                    type: 'delete',
                    entity_name: 'Oro\\Bundle\\CustomerBundle\\Entity\\CustomerUserAddress',
                    data_identifier: 'address.id',
                    name: 'delete',
                    frontend_type: 'delete-mass',
                    route: 'oro_datagrid_front_mass_action',
                    launcherOptions: {
                        iconClassName: 'fa-trash'
                    },
                    defaultMessages: {
                        confirm_title: 'oro.customer.mass_actions.delete_customer_addresses.confirm_title',
                        confirm_content: 'oro.customer.mass_actions.delete_customer_addresses.confirm_content',
                        confirm_ok: 'oro.customer.mass_actions.delete_customer_addresses.confirm_ok'
                    },
                    confirmMessages: {
                        selected_message: 'oro.customer.mass_actions.delete_customer_addresses.confirm_content'
                    },
                    messages: {
                        success: 'oro.customer.mass_actions.delete_customer_addresses.success_message'
                    },
                    token: 'twyTtWh-fGGmE6-MfM29WxfA5lnUEXfguCjafr8WDUQ',
                    handler: 'oro_datagrid.extension.mass_action.handler.delete',
                    frontend_handle: 'ajax',
                    route_parameters: [

                    ],
                    confirmation: true,
                    allowedRequestTypes: [
                        'POST',
                        'DELETE'
                    ],
                    requestType: 'POST'
                }
            },
            rowActions: {
                'show_map': {
                    label: 'Map',
                    type: 'map',
                    icon: 'map-o',
                    frontend_type: 'frontend-map',
                    name: 'show_map'
                },
                'update': {
                    label: 'Edit',
                    type: 'navigate',
                    link: 'update_link',
                    icon: 'pencil',
                    name: 'update',
                    frontend_type: 'navigate',
                    launcherOptions: {
                        onClickReturnValue: false,
                        runAction: true,
                        className: 'no-hash'
                    }
                },
                'delete': {
                    type: 'button-widget',
                    label: 'Delete',
                    rowAction: false,
                    link: '#',
                    icon: 'trash-o',
                    order: 520,
                    name: 'delete',
                    frontend_type: 'button-widget',
                    launcherOptions: {
                        onClickReturnValue: true,
                        runAction: true,
                        className: 'no-hash',
                        widget: [

                        ],
                        messages: [

                        ]
                    }
                },
                'oro_customer_frontend_address_delete': {
                    type: 'button-widget',
                    label: 'Delete',
                    rowAction: false,
                    link: '#',
                    icon: 'trash',
                    order: 520,
                    name: 'oro_customer_frontend_address_delete',
                    frontend_type: 'button-widget',
                    launcherOptions: {
                        onClickReturnValue: true,
                        runAction: true,
                        className: 'no-hash',
                        widget: [

                        ],
                        messages: [

                        ]
                    }
                }
            },
            initialState: {},
            state: {},
            gridViews: {
                views: [
                    {
                        name: '__all__',
                        label: '__all__',
                        icon: 'fa-table',
                        appearanceType: 'grid',
                        appearanceData: [

                        ],
                        type: 'system',
                        filters: [

                        ],
                        sorters: [

                        ],
                        columns: [

                        ],
                        editable: false,
                        deletable: false,
                        is_default: false,
                        shared_by: null
                    }
                ],
                gridName: 'frontend-customer-customer-address-grid',
                permissions: {
                    VIEW: true,
                    CREATE: true,
                    EDIT: true,
                    DELETE: true,
                    SHARE: true,
                    EDIT_SHARED: true
                }
            },
            filters: [
                {
                    name: 'street',
                    label: 'Customer Address',
                    choices: [
                        {
                            attr: [

                            ],
                            label: 'contains',
                            value: '1',
                            data: 1
                        },
                        {
                            attr: [

                            ],
                            label: 'does not contain',
                            value: '2',
                            data: 2
                        },
                        {
                            attr: [

                            ],
                            label: 'is equal to',
                            value: '3',
                            data: 3
                        },
                        {
                            attr: [

                            ],
                            label: 'starts with',
                            value: '4',
                            data: 4
                        },
                        {
                            attr: [

                            ],
                            label: 'ends with',
                            value: '5',
                            data: 5
                        },
                        {
                            attr: [

                            ],
                            label: 'is any of',
                            value: '6',
                            data: 6
                        },
                        {
                            attr: [

                            ],
                            label: 'is not any of',
                            value: '7',
                            data: 7
                        },
                        {
                            attr: [

                            ],
                            label: 'is empty',
                            value: 'filter_empty_option',
                            data: 'filter_empty_option'
                        },
                        {
                            attr: [

                            ],
                            label: 'is not empty',
                            value: 'filter_not_empty_option',
                            data: 'filter_not_empty_option'
                        }
                    ],
                    type: 'string',
                    enabled: true,
                    visible: true,
                    translatable: true,
                    force_like: false,
                    case_insensitive: true,
                    min_length: 0,
                    max_length: 9223372036854775807,
                    lazy: false,
                    cacheId: null
                },
                {
                    name: 'city',
                    label: 'City',
                    choices: [
                        {
                            attr: [

                            ],
                            label: 'contains',
                            value: '1',
                            data: 1
                        },
                        {
                            attr: [

                            ],
                            label: 'does not contain',
                            value: '2',
                            data: 2
                        },
                        {
                            attr: [

                            ],
                            label: 'is equal to',
                            value: '3',
                            data: 3
                        },
                        {
                            attr: [

                            ],
                            label: 'starts with',
                            value: '4',
                            data: 4
                        },
                        {
                            attr: [

                            ],
                            label: 'ends with',
                            value: '5',
                            data: 5
                        },
                        {
                            attr: [

                            ],
                            label: 'is any of',
                            value: '6',
                            data: 6
                        },
                        {
                            attr: [

                            ],
                            label: 'is not any of',
                            value: '7',
                            data: 7
                        },
                        {
                            attr: [

                            ],
                            label: 'is empty',
                            value: 'filter_empty_option',
                            data: 'filter_empty_option'
                        },
                        {
                            attr: [

                            ],
                            label: 'is not empty',
                            value: 'filter_not_empty_option',
                            data: 'filter_not_empty_option'
                        }
                    ],
                    type: 'string',
                    enabled: true,
                    visible: true,
                    translatable: true,
                    force_like: false,
                    case_insensitive: true,
                    min_length: 0,
                    max_length: 9223372036854775807,
                    lazy: false,
                    cacheId: null
                },
                {
                    name: 'state',
                    label: 'State',
                    choices: [
                        {
                            attr: [

                            ],
                            label: 'contains',
                            value: '1',
                            data: 1
                        },
                        {
                            attr: [

                            ],
                            label: 'does not contain',
                            value: '2',
                            data: 2
                        },
                        {
                            attr: [

                            ],
                            label: 'is equal to',
                            value: '3',
                            data: 3
                        },
                        {
                            attr: [

                            ],
                            label: 'starts with',
                            value: '4',
                            data: 4
                        },
                        {
                            attr: [

                            ],
                            label: 'ends with',
                            value: '5',
                            data: 5
                        },
                        {
                            attr: [

                            ],
                            label: 'is any of',
                            value: '6',
                            data: 6
                        },
                        {
                            attr: [

                            ],
                            label: 'is not any of',
                            value: '7',
                            data: 7
                        },
                        {
                            attr: [

                            ],
                            label: 'is empty',
                            value: 'filter_empty_option',
                            data: 'filter_empty_option'
                        },
                        {
                            attr: [

                            ],
                            label: 'is not empty',
                            value: 'filter_not_empty_option',
                            data: 'filter_not_empty_option'
                        }
                    ],
                    type: 'string',
                    enabled: true,
                    visible: true,
                    translatable: true,
                    force_like: false,
                    case_insensitive: true,
                    min_length: 0,
                    max_length: 9223372036854775807,
                    lazy: false,
                    cacheId: null
                },
                {
                    name: 'zip',
                    label: 'Zip\/Postal Code',
                    choices: [
                        {
                            attr: [

                            ],
                            label: 'contains',
                            value: '1',
                            data: 1
                        },
                        {
                            attr: [

                            ],
                            label: 'does not contain',
                            value: '2',
                            data: 2
                        },
                        {
                            attr: [

                            ],
                            label: 'is equal to',
                            value: '3',
                            data: 3
                        },
                        {
                            attr: [

                            ],
                            label: 'starts with',
                            value: '4',
                            data: 4
                        },
                        {
                            attr: [

                            ],
                            label: 'ends with',
                            value: '5',
                            data: 5
                        },
                        {
                            attr: [

                            ],
                            label: 'is any of',
                            value: '6',
                            data: 6
                        },
                        {
                            attr: [

                            ],
                            label: 'is not any of',
                            value: '7',
                            data: 7
                        },
                        {
                            attr: [

                            ],
                            label: 'is empty',
                            value: 'filter_empty_option',
                            data: 'filter_empty_option'
                        },
                        {
                            attr: [

                            ],
                            label: 'is not empty',
                            value: 'filter_not_empty_option',
                            data: 'filter_not_empty_option'
                        }
                    ],
                    type: 'string',
                    enabled: true,
                    visible: true,
                    translatable: true,
                    force_like: false,
                    case_insensitive: true,
                    min_length: 0,
                    max_length: 9223372036854775807,
                    lazy: false,
                    cacheId: null
                },
                {
                    name: 'countryName',
                    label: 'Country',
                    choices: [
                        {
                            attr: [

                            ],
                            label: 'contains',
                            value: '1',
                            data: 1
                        },
                        {
                            attr: [

                            ],
                            label: 'does not contain',
                            value: '2',
                            data: 2
                        },
                        {
                            attr: [

                            ],
                            label: 'is equal to',
                            value: '3',
                            data: 3
                        },
                        {
                            attr: [

                            ],
                            label: 'starts with',
                            value: '4',
                            data: 4
                        },
                        {
                            attr: [

                            ],
                            label: 'ends with',
                            value: '5',
                            data: 5
                        },
                        {
                            attr: [

                            ],
                            label: 'is any of',
                            value: '6',
                            data: 6
                        },
                        {
                            attr: [

                            ],
                            label: 'is not any of',
                            value: '7',
                            data: 7
                        },
                        {
                            attr: [

                            ],
                            label: 'is empty',
                            value: 'filter_empty_option',
                            data: 'filter_empty_option'
                        },
                        {
                            attr: [

                            ],
                            label: 'is not empty',
                            value: 'filter_not_empty_option',
                            data: 'filter_not_empty_option'
                        }
                    ],
                    type: 'string',
                    enabled: true,
                    visible: true,
                    translatable: true,
                    force_like: false,
                    case_insensitive: true,
                    min_length: 0,
                    max_length: 9223372036854775807,
                    lazy: false,
                    cacheId: null
                }
            ],
            columns: [
                {
                    label: 'Customer Address',
                    type: 'string',
                    translatable: true,
                    editable: false,
                    shortenableLabel: true,
                    name: 'street',
                    order: 0,
                    renderable: true,
                    sortable: true
                },
                {
                    label: 'City',
                    type: 'string',
                    translatable: true,
                    editable: false,
                    shortenableLabel: true,
                    name: 'city',
                    order: 1,
                    renderable: true,
                    sortable: true
                },
                {
                    label: 'State',
                    type: 'string',
                    translatable: true,
                    editable: false,
                    shortenableLabel: true,
                    name: 'state',
                    order: 2,
                    renderable: true,
                    sortable: true
                },
                {
                    label: 'Zip\/Postal Code',
                    type: 'string',
                    translatable: true,
                    editable: false,
                    shortenableLabel: true,
                    name: 'zip',
                    order: 3,
                    renderable: true,
                    sortable: true
                },
                {
                    label: 'Country',
                    type: 'string',
                    translatable: true,
                    editable: false,
                    shortenableLabel: true,
                    name: 'countryName',
                    order: 4,
                    renderable: true,
                    sortable: true
                },
                {
                    label: 'Type',
                    type: 'html',
                    translatable: true,
                    editable: false,
                    shortenableLabel: true,
                    name: 'types',
                    order: 5,
                    renderable: true
                }
            ],
            gridParams: {},
            enableFloatingHeaderPlugin: false
        },
        data: {
            data: [
                {
                    street: '45600 Marion Drive',
                    city: 'Winter Haven',
                    state: 'Florida',
                    zip: '33830',
                    countryName: 'United States',
                    types: '\n                \nDefault Billing, Default Shipping\n',
                    id: '2',
                    update_link: '\/customer\/user\/address\/customer\/2\/update\/2',
                    action_configuration: {
                        'delete': false,
                        'oro_customer_frontend_address_delete': {
                            hasDialog: false,
                            showDialog: true,
                            executionUrl: '\/ajax\/operation\/execute\/oro_customer_frontend_address_delete?' +
                            'entityClass=Oro%5CBundle%5CCustomerBundle%5CEntity%5CCustomerAddress&entityId=2&' +
                            'datagrid=frontend-customer-customer-address-grid&group%5B0%5D=&group%5B1%5D=' +
                            'datagridRowAction',
                            url: '\/ajax\/operation\/execute\/oro_customer_frontend_address_delete?entityClass=' +
                            'Oro%5CBundle%5CCustomerBundle%5CEntity%5CCustomerAddress&entityId=2&' +
                            'datagrid=frontend-customer-customer-address-grid&' +
                            'group%5B0%5D=&group%5B1%5D=datagridRowAction',
                            jsDialogWidget: 'oro\/dialog-widget',
                            executionTokenData: {
                                oro_action_operation_execution: {
                                    operation_execution_csrf_token: 'dBGU11ea-MobabLOQdLDfR13RqgC8mOkA5aVAP0FOQQ'
                                }
                            },
                            confirmation: {
                                title: 'oro.action.delete_entity',
                                message: 'oro.action.delete_confirm',
                                okText: 'oro.action.button.delete',
                                component: 'oroui\/js\/delete-confirmation',
                                message_parameters: {
                                    entityLabel: 'Customer Address'
                                }
                            }
                        }
                    }
                },
                {
                    street: '67800 Junkins Avenue',
                    city: 'Albany',
                    state: 'Georgia',
                    zip: '31707',
                    countryName: 'United States',
                    types: '\n                \nDefault Billing, Default Shipping\n',
                    id: '3',
                    update_link: '\/customer\/user\/address\/customer\/3\/update\/3',
                    action_configuration: {
                        'delete': false,
                        'oro_customer_frontend_address_delete': {
                            hasDialog: false,
                            showDialog: true,
                            executionUrl: '\/ajax\/operation\/execute\/oro_customer_frontend_address_delete' +
                            '?entityClass=Oro%5CBundle%5CCustomerBundle%5CEntity%5CCustomerAddress&entityId' +
                            '=3&datagrid=frontend-customer-customer-address-grid&group%5B0%5D=' +
                            '&group%5B1%5D=datagridRowAction',
                            url: '\/ajax\/operation\/execute\/oro_customer_frontend_address_delete?' +
                            'entityClass=Oro%5CBundle%5CCustomerBundle%5CEntity%5CCustomerAddress&entityId' +
                            '=3&datagrid=frontend-customer-customer-address-grid&group%5B0%5D=&' +
                            'group%5B1%5D=datagridRowAction',
                            jsDialogWidget: 'oro\/dialog-widget',
                            executionTokenData: {
                                oro_action_operation_execution: {
                                    operation_execution_csrf_token: 'xEq5Nx_L9e5z_TgcyGPdHfOQ1G7biOCkHMwHQylLTrY'
                                }
                            },
                            confirmation: {
                                title: 'oro.action.delete_entity',
                                message: 'oro.action.delete_confirm',
                                okText: 'oro.action.button.delete',
                                component: 'oroui\/js\/delete-confirmation',
                                message_parameters: {
                                    entityLabel: 'Customer Address'
                                }
                            }
                        }
                    }
                },
                {
                    street: '801 Scenic Hwy',
                    city: 'Haines City',
                    state: 'Florida',
                    zip: '33844',
                    countryName: 'United States',
                    types: '\n                \nDefault Billing, Default Shipping\n',
                    id: '1',
                    update_link: '\/customer\/user\/address\/customer\/1\/update\/1',
                    action_configuration: {
                        'delete': false,
                        'oro_customer_frontend_address_delete': {
                            hasDialog: false,
                            showDialog: true,
                            executionUrl: '\/ajax\/operation\/execute\/oro_customer_frontend_address_delete' +
                            '?entityClass=Oro%5CBundle%5CCustomerBundle%5CEntity%5CCustomerAddress&entityId' +
                            '=1&datagrid=frontend-customer-customer-address-grid&group%5B0%5D=&group%5B1%5D' +
                            '=datagridRowAction',
                            url: '\/ajax\/operation\/execute\/oro_customer_frontend_address_delete?' +
                            'entityClass=Oro%5CBundle%5CCustomerBundle%5CEntity%5CCustomerAddress&entityId' +
                            '=1&datagrid=frontend-customer-customer-address-grid&group%5B0%5D=&group%5B1%5D' +
                            '=datagridRowAction',
                            jsDialogWidget: 'oro\/dialog-widget',
                            executionTokenData: {
                                oro_action_operation_execution: {
                                    operation_execution_csrf_token: 'DyvMsszIl5O45XOd102VMKoergWzZFpeVPzqlFBmgdI'
                                }
                            },
                            confirmation: {
                                title: 'oro.action.delete_entity',
                                message: 'oro.action.delete_confirm',
                                okText: 'oro.action.button.delete',
                                component: 'oroui\/js\/delete-confirmation',
                                message_parameters: {
                                    entityLabel: 'Customer Address'
                                }
                            }
                        }
                    }
                }
            ],
            options: {
                hideToolbar: false,
                totalRecords: 3,
                totals: [

                ]
            }
        },
        enableFilters: true,
        enableToggleFilters: true,
        filterContainerSelector: '[data-grid-toolbar=\'top\'] [data-role=\'filter-container\']',
        filtersStateElement: null,
        enableViews: true,
        showViewsInNavbar: false,
        showViewsInCustomElement: false,
        inputName: false,
        themeOptions: {
            actionsDropdown: 'auto',
            actionOptions: {
                refreshAction: {
                    launcherOptions: {
                        className: 'btn btn--default btn--size-s refresh-action',
                        icon: 'undo fa--no-offset',
                        launcherMode: 'icon-only'
                    }
                },
                resetAction: {
                    launcherOptions: {
                        className: 'btn btn--default btn--size-s reset-action',
                        icon: 'refresh fa--no-offset',
                        launcherMode: 'icon-only'
                    }
                }
            },
            customModules: {
                datagridSettingsComponent: 'orofrontend\/js\/app\/views\/' +
                'datagrid-settings\/frontend-datagrid-settings-column-view'
            },
            toolbarTemplateSelector: '#template-customer-address-book-addresses-grid-toolbar',
            cellActionsHideCount: 4,
            cellLauncherOptions: {
                launcherMode: 'icon-only'
            }
        },
        toolbarOptions: {
            actionsPanel: {
                className: 'btn-group not-expand frontend-datagrid__panel'
            },
            itemsCounter: {
                className: 'datagrid-tool',
                transTemplate: 'oro_frontend.datagrid.pagination.totalRecords.companyAddresses'
            },
            hideItemsCounter: false
        },
        gridViewsOptions: {
            text: 'oro.customer.customer_address_book.customer_addresses',
            icon: 'map-marker'
        },
        gridBuildersOptions: []
    };
});
