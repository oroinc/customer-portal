@regression
@ticket-BB-17341
@fixture-OroCustomerBundle:CustomerParentBreadcrumbsFixture.yml

Feature: Customer Parent Breadcrumbs
  In order to navigate between parent and children customers
  As an administrator
  I should see parent link with full path and subsidiaries grid on view page

  Scenario: Check customer parent and subsidiaries on view page
    Given I login as administrator
    And I go to Customers / Customers
    When I click view last-level customer in grid
    Then there is no records in "Subsidiaries Grid"
    And I should see customer with:
      | Name            | last-level customer                        |
      | Parent Customer | root customer / ... / forth-level customer |
    When I click "..."
    Then I should see customer with:
      | Parent Customer | root customer / second-level customer / third-level customer / forth-level customer |

    When I click "forth-level customer"
    Then I should see following records in "Subsidiaries Grid":
      | last-level customer |
    And I should see customer with:
      | Name            | forth-level customer                       |
      | Parent Customer | root customer / ... / third-level customer |
    When I click "..."
    Then I should see customer with:
      | Parent Customer | root customer / second-level customer / third-level customer |

    When I click "third-level customer"
    Then I should see following records in "Subsidiaries Grid":
      | forth-level customer |
    And I should see customer with:
      | Name            | third-level customer                  |
      | Parent Customer | root customer / second-level customer |

    When I click "second-level customer"
    Then I should see following records in "Subsidiaries Grid":
      | third-level customer |
    And I should see customer with:
      | Name            | second-level customer |
      | Parent Customer | root customer         |

    When I click "root customer"
    Then I should see following records in "Subsidiaries Grid":
      | second-level customer |
    And I should see customer with:
      | Name            | root customer |
      | Parent Customer | N/A           |

    When I click view second-level customer in "Subsidiaries Grid"
    And I should see customer with:
      | Name | second-level customer |
