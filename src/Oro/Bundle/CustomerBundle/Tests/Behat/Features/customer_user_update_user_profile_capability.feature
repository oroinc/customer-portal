@regression
@fixture-OroCustomerBundle:BuyerCustomerFixture.yml
Feature: Customer User Update User Profile capability
  In order to control for customer users ability to update their own profile
  As administrator
  I need to have ability to manage this permission independently of customer user entity permissions

  Scenario: Update User Profile capability is on and User permissions are Corporate for Administrator Role by default
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |
    And I proceed as the Admin
    And I login as administrator
    And I open "Administrator" customer user role view page
    Then the role has following active permissions:
      | Customer User | View:Сorporate (All Levels) | Edit:Сorporate (All Levels) |
    And following capability permissions should be checked:
      | Update User Profile |

  Scenario: User can update profile only when Update User Profile capability is on
    Given I proceed as the User
    And I signed in as NancyJSallee@example.org on the store frontend
    When I follow "Account"
    Then I should see an "My Profile Menu Item" element
    When I click on "My Profile Menu Item"
    And I click "Edit Profile Button"
    And I fill form with:
      | First Name | Barbara |
    And I save form
    Then I should see "Customer User profile updated" flash message
    And I should see "Signed in as: Barbara Sallee"
    And I click "Edit Profile Button"
    # Disable "Update User Profile" capability
    Given I proceed as the Admin
    And I should be on Customer User Role View page
    When I click "Edit"
    And I uncheck "Update User Profile" entity permission
    And I save and close form
    Then I should see "Customer User Role has been saved" flash message
    And the role has following active permissions:
      | Customer User | View:Сorporate (All Levels) | Edit:Сorporate (All Levels) |
    And following capability permissions should be unchecked:
      | Update User Profile |

  Scenario: User can't update profile without "Update User Profile" capability
    Given I proceed as the User
    And I should be on Customer User Profile Update page
    And I fill form with:
      | First Name | Kate |
    And I save form
    Then I should see "You do not have permission to perform this action"
    And I scroll to top
    When I follow "Account"
    Then I should see an "My Profile Menu Item" element

  Scenario: Turn on Update User Profile capability and set Customer User permissions to None
    Given I proceed as the Admin
    And I should be on Customer User Role View page
    When I click "Edit"
    And I check "Update User Profile" entity permission
    And select following permissions:
      | Customer User | View:None | Edit:None |
    And I save and close form
    Then I should see "Customer User Role has been saved" flash message
    And the role has following active permissions:
      | Customer User | View:None | Edit:None |
    And following capability permissions should be checked:
      | Update User Profile |

  Scenario: User can update profile when Update User Profile capability is on and Customer User permissions is None
    Given I proceed as the User
    And I reload the page
    And I click "Edit Profile Button"
    And I fill form with:
      | First Name | Amanda |
    And I save form
    Then I should see "Customer User profile updated" flash message
    And I should see "Signed in as: Amanda Sallee"
    And I click "Edit Profile Button"

  Scenario: Turn off Update User Profile capability and set Customer User permissions to None
    Given I proceed as the Admin
    And I should be on Customer User Role View page
    When I click "Edit"
    And I uncheck "Update User Profile" entity permission
    And I save and close form
    Then I should see "Customer User Role has been saved" flash message
    And the role has following active permissions:
      | Customer User | View:None | Edit:None |
    And following capability permissions should be unchecked:
      | Update User Profile |

  Scenario: User can't update profile when Update User Profile capability is off and User Edit permission is None
    Given I proceed as the User
    And I should be on Customer User Profile Update page
    And I fill form with:
      | First Name | Kate |
    And I save form
    Then I should see "You do not have permission to perform this action"
    And I scroll to top
    When I follow "Account"
    Then I should see an "My Profile Menu Item" element
