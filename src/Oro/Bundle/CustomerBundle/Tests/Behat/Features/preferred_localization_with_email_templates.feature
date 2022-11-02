@regression
@ticket-BB-16597
@fixture-OroLocaleBundle:ZuluLocalization.yml
@fixture-OroCustomerBundle:CustomerFixture.yml

Feature: Preferred localization with email templates
  Verify the possibility of select priority localization in the customer user emails

  Scenario: Feature Background
    Given I login as administrator

  Scenario: Configure localization
    Given I go to System/Configuration
    And follow "System Configuration/General Setup/Localization" on configuration sidebar
    And fill "Configuration Localization Form" with:
      | Enabled Localizations | [English (United States), Zulu_Loc] |
      | Default Localization  | English (United States)             |
    When I click "Save settings"
    Then I should see "Configuration saved" flash message

  Scenario: Update email template
    Given I go to System / Emails / Templates
    And filter Template Name as is equal to "customer_user_welcome_email_registered_by_admin"
    And click "edit" on first row in grid
    And click "Zulu"
    When I fill "Email Template Form" with:
      | Subject Fallback | false        |
      | Content Fallback | false        |
      | Subject          | Zulu Subject |
      | Content          | Zulu Body    |
    And save and close form
    Then I should see "Template saved" flash message

  Scenario: Create customer user and verify email localization
    Given I go to Customers / Customer Users
    And click "Create Customer User"
    When I fill form with:
      | First Name    | FirstName           |
      | Last Name     | LastName            |
      | Email Address | example@example.org |
    And focus on "Birthday" field
    And click "Today"
    And fill form with:
      | Customer               | WithCustomerUser |
      | Generate Password      | true             |
      | Send Welcome Email     | true             |
      | Buyer (Predefined)     | true             |
      | Preferred Localization | Zulu_Loc         |
    And save and close form
    Then I should see "Customer User has been saved" flash message
    And email with Subject "Zulu Subject" containing the following was sent:
      | Body | Zulu Body |
