@fixture-OroCustomerBundle:CustomerUserFixture.yml
@ticket-BAP-11576
@ticket-BAP-21323

Feature: Customer User Login Attempts
  In order to have ability to manage customer user logins
  As administrator
  I need to have ability see the list of login attempts

  Scenario: Try to login with wrong customer user
    Given I am on the homepage
    And I click "Sign In"
    And I fill form with:
      | Email Address | NotExistingAddress@example.com |
      | Password      | test                           |
    When I click "Sign In"

  Scenario: Login with customer user
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I should see text matching "Signed in as: Amanda Cole"

  Scenario: Customer user login attempts
    Given I login as administrator
    And go to Customers/Customer User Login Attempts
    Then there are 2 records in grid
    And I should see following grid:
      | Success | Source  | Username                       | Customer User |
      | Yes     | Default | AmandaRCole@example.org        | Amanda Cole   |
      | No      | Default | NotExistingAddress@example.com |               |

  Scenario: Check users attempts grid "Username" filter
    When I set filter "Username" as is equal to "NotExistingAddress@example.com" and press Enter key
    Then I should see following grid:
      | Success | Source  | Username                       | Customer User |
      | No      | Default | NotExistingAddress@example.com |               |
    When I set filter "Username" as is equal to "AmandaRCole@example.org" and press Enter key
    Then I should see following grid:
      | Success | Source  | Username                       | Customer User |
      | Yes     | Default | AmandaRCole@example.org        | Amanda Cole   |
    When I reset "Username" filter
    Then there are 2 records in grid

  Scenario: Check users attempts grid "Source" filter
    When I check "Impersonation" in Source filter
    Then there are 0 records in grid
    And I reset "Source" filter

  Scenario: Check users attempts grid "Customer user" filter
    When I choose "Amanda Cole" in the Customer user filter
    Then I should see following grid:
      | Success | Source  | Username                       | Customer User |
      | Yes     | Default | AmandaRCole@example.org        | Amanda Cole   |
    When I reset "Customer user" filter
    Then there are 2 records in grid

  Scenario: Sort by Success field
    When I sort grid by "Success"
    Then I should see following grid:
      | Success | Source  | Username                       | Customer User |
      | No      | Default | NotExistingAddress@example.com |               |
      | Yes     | Default | AmandaRCole@example.org        | Amanda Cole   |
    When I sort grid by "Success"
    Then I should see following grid:
      | Success | Source  | Username                       | Customer User |
      | Yes     | Default | AmandaRCole@example.org        | Amanda Cole   |
      | No      | Default | NotExistingAddress@example.com |               |

  Scenario: Sort by Username field
    When I sort grid by "Username"
    Then I should see following grid:
      | Success | Source  | Username                       | Customer User |
      | Yes     | Default | AmandaRCole@example.org        | Amanda Cole   |
      | No      | Default | NotExistingAddress@example.com |               |
    When I sort grid by "Username"
    Then I should see following grid:
      | Success | Source  | Username                       | Customer User |
      | No      | Default | NotExistingAddress@example.com |               |
      | Yes     | Default | AmandaRCole@example.org        | Amanda Cole   |
    When I sort grid by "Username"
    Then I should see following grid:
      | Success | Source  | Username                       | Customer User |
      | Yes     | Default | AmandaRCole@example.org        | Amanda Cole   |
      | No      | Default | NotExistingAddress@example.com |               |

  Scenario: Sort by Attempt at field
    When I sort grid by "Attempt at"
    Then I should see following grid:
      | Success | Source  | Username                       | Customer User |
      | No      | Default | NotExistingAddress@example.com |               |
      | Yes     | Default | AmandaRCole@example.org        | Amanda Cole   |
    When I sort grid by "Attempt at"
    Then I should see following grid:
      | Success | Source  | Username                       | Customer User |
      | Yes     | Default | AmandaRCole@example.org        | Amanda Cole   |
      | No      | Default | NotExistingAddress@example.com |               |

  Scenario: Sort by Customer User field
    When I sort grid by "Customer User"
    Then I should see following grid:
      | Success | Source  | Username                       | Customer User |
      | No      | Default | NotExistingAddress@example.com |               |
      | Yes     | Default | AmandaRCole@example.org        | Amanda Cole   |
    When I sort grid by "Customer User"
    Then I should see following grid:
      | Success | Source  | Username                       | Customer User |
      | Yes     | Default | AmandaRCole@example.org        | Amanda Cole   |
      | No      | Default | NotExistingAddress@example.com |               |
