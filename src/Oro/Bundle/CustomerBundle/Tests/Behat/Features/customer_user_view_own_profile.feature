@ticket-BB-16410
@fixture-OroCustomerBundle:CustomerUserAddressNancyJSalleeFixture.yml
Feature: Customer user view own profile

  Scenario: Anonymous user can not see any profile
    Given I go to "/customer/profile"
    Then I should be on Customer User Login page

  Scenario: User can see own profile
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account Dropdown"
    Then I should see "My Profile"
