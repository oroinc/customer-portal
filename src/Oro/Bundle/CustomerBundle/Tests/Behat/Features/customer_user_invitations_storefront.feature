@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
@fixture-OroCustomerBundle:CustomerUserRoleFixture.yml

Feature: Customer user invitations storefront
  In order to let new users provide their own profile details
  As a customer account administrator
  I want to invite users and manage open invitations on the Users page

  Background:
    Given I change configuration options:
      | oro_customer.customer_user_invitations_enabled | true |

  Scenario: Invite a user and let them complete their account
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Users"
    And I click "Invite User Dropdown Toggle"
    When I click "Invite User"
    Then I should see "Invite Customer User"
    And I should see a "Customer User Invitation Form" element
    When I fill "Customer User Invitation Form" with:
      | Email              | invited-user@example.org |
      | Buyer (Predefined) | true                     |
    And I click "Send Invitation"
    Then I should see "The customer user invitation has been sent." flash message
    And I should see "invited-user@example.org" in the "Customer User Invitations Grid" element
    And email with Subject "You have been invited to first customer" containing the following was sent:
      | To   | invited-user@example.org |
      | Body | Accept invitation        |
    And I remember "Accept invitation" link from the email
    When I click "Account Dropdown"
    And I click "Sign Out"
    And I follow remembered "Accept invitation" link from the email
    Then I should see "Complete Your Account"
    And I should see a "Customer User Invitation Acceptance Form" element
    When I fill "Customer User Invitation Acceptance Form" with:
      | First Name       | Invited         |
      | Last Name        | User            |
      | Password         | InvitedUser123! |
      | Confirm Password | InvitedUser123! |
    And I click "Create Account"
    Then I should see "Your account has been created successfully." flash message

  Scenario: Resend and revoke an open invitation
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Users"
    And I click "Invite User Dropdown Toggle"
    And I click "Invite User"
    And I fill "Customer User Invitation Form" with:
      | Email              | revoked-user@example.org |
      | Buyer (Predefined) | true                     |
    And I click "Send Invitation"
    Then I should see "revoked-user@example.org" in the "Customer User Invitations Grid" element
    When I click "Resend" on row "revoked-user@example.org" in "Customer User Invitations Grid"
    Then I should see "The customer user invitation has been resent." flash message
    When I click "Revoke" on row "revoked-user@example.org" in "Customer User Invitations Grid"
    And I click "Revoke" in confirmation dialogue
    Then I should see "The customer user invitation has been revoked." flash message
    And I should not see "revoked-user@example.org" in the "Customer User Invitations Grid" element

  Scenario: An existing customer user cannot be invited again
    Given I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Users"
    And I click "Invite User Dropdown Toggle"
    And I click "Invite User"
    When I fill "Customer User Invitation Form" with:
      | Email              | AmandaRCole@example.org |
      | Buyer (Predefined) | true                    |
    And I click "Send Invitation"
    Then I should see "This email address cannot be invited."

  Scenario: Disabling the feature hides invitations from the Users page
    Given I change configuration options:
      | oro_customer.customer_user_invitations_enabled | false |
    And I signed in as NancyJSallee@example.org on the store frontend
    And I click "Account Dropdown"
    When I click "Users"
    Then I should not see "Open Invitations"
    And I should not see an "Invite User Dropdown Toggle" element
    And I change configuration options:
      | oro_customer.customer_user_invitations_enabled | true |
