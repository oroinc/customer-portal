@fixture-MassActionsCustomerAddressFixture.yml

Feature:
    Scenario: Check titles for mass actions
        Given I signed in as AmandaRCole@example.org on the store frontend
        And I click "Account"
        And I click "Roles"
        Then I click edit Administrator in grid
        When select following permissions:
            | Customer User Address | Edit:Сorporate |
        And I save form
        Then I should see "Customer User Role has been saved" flash message
        And the role has following active permissions:
            | Customer User Address | Edit:Сorporate (All Levels) |
        And I click "Account"
        And I click "Address Book"
        Then I should see only following actions for row #2 on "Customer Company Addresses Grid" grid:
            | Map |
            | Delete |
        Then I should see only following actions for row #2 on "Customer Company User Addresses Grid" grid:
            | Map |
            | Delete |
        And I click "Sign Out"
