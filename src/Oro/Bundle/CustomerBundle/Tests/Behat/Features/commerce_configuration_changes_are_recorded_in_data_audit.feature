@skip
@regression
@fixture-OroCustomerBundle:CustomerUserAmandaRCole.yml
Feature: Commerce configuration changes are recorded in Data Audit
  In order to keep an audit trail of configuration changes at every commerce level
  As an Administrator
  I need organization, website, customer group and customer configuration changes to appear in
  Data Audit as their own entity types, filterable by Entity Type

  # "Enable Search History Reporting" exists at the organization, website, customer group and customer
  # scopes and is enabled by default. The scopes inherit down the chain
  # (organization < website < customer group < customer), so each scenario flips the value relative to
  # the value inherited from its parent to guarantee an actual change (and therefore an audit entry).

  Scenario: An organization-level change is audited as "Configuration: Organization"
    Given I login as administrator
    When I go to System/User Management/Organizations
    And I click "Configuration" on row "ORO" in grid
    And I follow "Commerce/Search/Search Terms" on configuration sidebar
    And uncheck "Use System" for "Enable Search History Reporting" field
    And I check "Enable Search History Reporting"
    And I click "Save settings"
    Then I should see "Configuration saved" flash message
    When I go to System/ Data Audit
    Then I should see "Configuration: Organization" in grid
    And I should see "Enable Search History Reporting" in grid

  Scenario: A website-level change is audited as "Configuration: Website"
    Given I go to System/Websites
    And I click "Configuration" on row "Default" in grid
    And I follow "Commerce/Search/Search Terms" on configuration sidebar
    And uncheck "Use Organization" for "Enable Search History Collection" field
    And I uncheck "Enable Search History Collection"
    And I click "Save settings"
    Then I should see "Configuration saved" flash message
    When I go to System/ Data Audit
    Then I should see "Configuration: Website" in grid

  Scenario: A customer-group-level change is audited as "Configuration: Customer Group"
    Given I go to Customers/Customer Groups
    And I click "Configuration" on row "AmandaRColeGroup" in grid
    And I follow "Commerce/Search/Search Terms" on configuration sidebar
    And uncheck "Use Website" for "Enable Search History Collection" field
    And I uncheck "Enable Search History Collection"
    And I click "Save settings"
    Then I should see "Configuration saved" flash message
    When I go to System/ Data Audit
    Then I should see "Configuration: Customer Group" in grid

  Scenario: A customer-level change is audited as "Configuration: Customer"
    Given I go to Customers/Customers
    And I click "Configuration" on row "AmandaRCole" in grid
    And I follow "Commerce/Search/Search Terms" on configuration sidebar
    And uncheck "Use Customer Group" for "Enable Search History Collection" field
    And I check "Enable Search History Collection"
    And I click "Save settings"
    Then I should see "Configuration saved" flash message
    When I go to System/ Data Audit
    Then I should see "Configuration: Customer" in grid

  Scenario: Data Audit is filtered by the commerce configuration Entity Types
    Given I go to System/ Data Audit
    When I check "Configuration: Organization" in "Entity Type" filter
    And I check "Configuration: Website" in "Entity Type" filter
    And I check "Configuration: Customer Group" in "Entity Type" filter
    And I check "Configuration: Customer" in "Entity Type" filter
    Then I should see "Configuration: Organization" in grid
    And I should see "Configuration: Website" in grid
    And I should see "Configuration: Customer Group" in grid
    And I should see "Configuration: Customer" in grid
