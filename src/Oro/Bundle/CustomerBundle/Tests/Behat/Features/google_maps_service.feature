@fixture-OroCustomerBundle:AddressBookFixture.yml
@regression

Feature: Google maps service
  In order to be able to manager address book on front store
  As a Buyer
  I need to have ability to see address location on Google map preview

  Scenario: Check google maps error message on my profile page when address is not valid
    Given I signed in as AmandaRCole@example.org on the store frontend
    When I follow "Account"
    Then I should see "The address is not recognized. Please check the provided address information."

  Scenario: Check google maps shows on my profile
    Given I follow "Account"
    When I click on "Address Item" with title "23400 Caldwell Road"
    Then I should see "Map Container" element inside "Default Addresses" element

  Scenario: Check google maps shows on address grid
    Given I click "Address Book"
    When I click Map "34500 Capitol Avenue" in grid
    Then I should see an "Map Popover" element
