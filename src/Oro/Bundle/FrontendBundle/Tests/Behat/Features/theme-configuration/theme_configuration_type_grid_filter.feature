@regression
@fixture-OroThemeBundle:theme_configuration.yml

Feature: Theme Configuration Type Grid Filter

  Scenario: Enable & Check Type filter
    Given I login as administrator
    When I go to System / Theme Configurations
    Then records in grid should be 4
    When I show filter "Type" in "Theme Configurations Grid" grid
    And I check "Storefront" in "Type: All" filter strictly
    Then I should see following grid:
      | Name            |
      | Golden Carbon   |
      | Refreshing Teal |
      | Default         |
      | Custom          |
    And records in grid should be 4
    And I reset "Type: Storefront" filter

  Scenario: Enable column "Type" and Sort by it
    Given I should see following grid:
      | Name            |
      | Golden Carbon   |
      | Refreshing Teal |
      | Default         |
      | Custom          |
    And I show column Type in grid
    When I sort grid by "Type"
    Then I should see following grid:
      | Name            | Type       |
      | Golden Carbon   | storefront |
      | Refreshing Teal | storefront |
      | Default         | storefront |
      | Custom          | storefront |
    When I sort grid by "Type" again
    Then I should see following grid:
      | Name            | Type       |
      | Custom          | storefront |
      | Default         | storefront |
      | Refreshing Teal | storefront |
      | Golden Carbon   | storefront |
    And I reset "Theme Configurations Grid" grid
