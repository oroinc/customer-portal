@fixture-OroCustomerBundle:BuyerCustomerFixture.yml

Feature: Customer user invitations back office
  In order to invite users on behalf of a customer account administrator
  As a back-office administrator
  I want to create and manage customer user invitations

  Scenario: Create an invitation on behalf of a customer user
    Given I change configuration options:
      | oro_customer.customer_user_invitations_enabled | true |
    And I login as administrator
    And I go to Customers / Customer User Invitations
    When I click "Invite User"
    Then I should see "Invite Customer User"
    When I fill form with:
      | Email                      | back-office-invite@example.org |
      | Customer                   | first customer                 |
      | Customer User (Invited By) | Nancy JSallee                  |
      | Buyer (Predefined)         | true                           |
    And I click "Send Invitation"
    Then I should see "The customer user invitation has been sent." flash message
    And I should see back-office-invite@example.org in grid with following data:
      | Customer | first customer |
      | Status   | Pending        |

  Scenario: Revoke and delete an invitation
    Given I login as administrator
    And I go to Customers / Customer User Invitations
    When I click "Revoke" on row "back-office-invite@example.org" in grid
    And I click "Revoke" in confirmation dialogue
    Then I should see "The customer user invitation has been revoked." flash message
    And I should see back-office-invite@example.org in grid with following data:
      | Status | Revoked |
    When I click Delete back-office-invite@example.org in grid
    And I confirm deletion
    Then I should not see "back-office-invite@example.org"
