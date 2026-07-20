@regression
@feature-BAP-23397
@fixture-OroCustomerBundle:CustomerAddressBackofficeSearchFixture.yml

Feature: Customer address back-office search redirect

    Scenario: Open the customer view page from a customer address search result
        Given I login as administrator
        When I type "118 Northeast Drive" in "search"
        And I click "Search Submit"
        Then I should be on Search Result page
        And I should see "118 Northeast Drive"
        When I follow "118 Northeast Drive"
        Then I should see "Backoffice Search Customer"
