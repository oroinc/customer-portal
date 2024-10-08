@ticket-BB-16377
@fixture-OroCustomerBundle:FrontendGridViewsFixture.yml
@fixture-OroCustomerBundle:NotEnabledNotConfirmedCustomerUserFixture.yml

Feature: Frontend grid boolean filter for customer users
  In order to manage customer users in frontend grid
  As a Buyer
  I want to be able filter customer users by enabled or confirmed columns

  Scenario: Check filter by enabled
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Account Dropdown"
    And I click "Users"
    When I check "Yes" in "Enabled" filter
    Then I should see following grid:
      | First Name  | Last Name  | Email Address           | Enabled |
      | Amanda      | Cole       | AmandaRCole@example.org | Yes     |
      | FirstName_1 | LastName_1 | user_1@example.org      | Yes     |
      | FirstName_2 | LastName_2 | user_2@example.org      | Yes     |
      | FirstName_3 | LastName_3 | user_3@example.org      | Yes     |

    When I check "No" in "Enabled" filter
    Then I should see following grid:
      | First Name            | Last Name            | Email Address                | Enabled |
      | FirstName_not_enabled | LastName_not_enabled | user_not_enabled@example.org | No      |

    When I check "All" in "Enabled" filter
    Then I should see following grid:
      | First Name            | Last Name            | Email Address                | Enabled |
      | Amanda                | Cole                 | AmandaRCole@example.org      | Yes     |
      | FirstName_1           | LastName_1           | user_1@example.org           | Yes     |
      | FirstName_2           | LastName_2           | user_2@example.org           | Yes     |
      | FirstName_3           | LastName_3           | user_3@example.org           | Yes     |
      | FirstName_not_enabled | LastName_not_enabled | user_not_enabled@example.org | No      |

  Scenario: Check filter by confirmed
    When I check "Yes" in "Confirmed" filter
    Then I should see following grid:
      | First Name  | Last Name  | Email Address           | Confirmed |
      | Amanda      | Cole       | AmandaRCole@example.org | Yes       |
      | FirstName_1 | LastName_1 | user_1@example.org      | Yes       |
      | FirstName_2 | LastName_2 | user_2@example.org      | Yes       |
      | FirstName_3 | LastName_3 | user_3@example.org      | Yes       |

    When I check "No" in "Confirmed" filter
    Then I should see following grid:
      | First Name            | Last Name            | Email Address                | Confirmed |
      | FirstName_not_enabled | LastName_not_enabled | user_not_enabled@example.org | No        |

    When I check "All" in "Confirmed" filter
    Then I should see following grid:
      | First Name            | Last Name            | Email Address                | Confirmed |
      | Amanda                | Cole                 | AmandaRCole@example.org      | Yes       |
      | FirstName_1           | LastName_1           | user_1@example.org           | Yes       |
      | FirstName_2           | LastName_2           | user_2@example.org           | Yes       |
      | FirstName_3           | LastName_3           | user_3@example.org           | Yes       |
      | FirstName_not_enabled | LastName_not_enabled | user_not_enabled@example.org | No        |
