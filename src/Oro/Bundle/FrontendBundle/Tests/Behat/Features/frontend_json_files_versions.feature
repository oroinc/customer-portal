@ticket-BAP-20090

Feature: Frontend JSON files version
  In order to correctly work with CDNs
  As an Administrator
  I want to be sure that all loaded resources are versioned

  Scenario: Feature background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Check All JSON resources are versioned for storefront
    Given I proceed as the Buyer
    When I am on homepage
    Then I should be sure that all "json" assets are versioned

  Scenario: Check All JSON resources are versioned for admin area
    Given I proceed as the Admin
    And I login as administrator
    Then I should be sure that all "json" assets are versioned
