@fixture-OroCustomerBundle:AddressBookFixture.yml
@regression

Feature: Google maps service
  Check google maps error messages

  Scenario: Check google maps error message on my profile page when address is not valid
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account"
    Then I should see "The address is not recognized. Please check the provided address information."

  Scenario: Check google maps error message on my profile when api doesn't work
    Given I click "Account"
    When I click on "Address Item" with title "23400 Caldwell Road"
    Then I should see "The map cannot be displayed. Please try again later or contact your administrator."

  Scenario: Check error message on address grid
    Given I click "Address Book"
    And I click Map "34500 Capitol Avenue" in grid
    Then I should see "The map cannot be displayed. Please try again later or contact your administrator."
