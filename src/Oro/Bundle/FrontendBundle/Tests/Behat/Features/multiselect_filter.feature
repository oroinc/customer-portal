@ticket-BB-9574
@fixture-OroFrontendBundle:multi_select_filter.yml

Feature: Multi-select filter
  In order to provide a more convenient way for a user to make selection from the list of values in filters
  As an Frontend Theme Developer
  I want to be able to specify which template to use for rendering filter selector

  # Description
  # Provide a way for a frontend theme developer to use different template for select- and multi-select filters.
  # I.e. as a frontend developer, I should be able to specify that for the Size and Color filters
  # I want to use a different template (while other select and multi-select filter would use the default template,
  # specified in system configuration).
  # Implement the new template for select & multi-select filters.
  #
  # Configuration
  # Add new fieldset "Filter Settings" to the System -> Configuration -> COMMERCE -> Design -> Theme page:
  # new field "Value Selectors" - drop-down (values - "Drop-down", "All at once"),
  # default value - "Drop-down", scope - global/organization/website, hint:
  # The drop-down selector is better optimized for selecting from a long list of values.
  #
  # Acceptance Criteria
  # Show how an adminstrator can modify the default filter template
  # Show how a frontend developer can specify a specific template for some product attibutes

  Scenario: Create different window session
    Given sessions active:
      | User  |first_session  |
      | Admin |second_session |

  Scenario: Check default value "Drop-down" for multiselect filters on front store
    Given I proceed as the User
    And I signed in as AmandaRCole@example.org on the store frontend
    And I follow "Account"
    And I click "Requests For Quote"
    And I click "Filters Dropdown"
    When I click "Filter By Step"
    Then I should see an "Filter Checkboxes" element

  Scenario: Check value "All at once" for multiselect filters on front store
    Given I proceed as the Admin
    And I login as administrator
    And go to System / Configuration
    And I follow "Commerce/Design/Theme" on configuration sidebar
    And fill "Filter Settings Form" with:
      | Use Default     | false       |
      | Value Selectors | All at once |
    And click "Save settings"
    When I proceed as the User
    And I reload the page
    And I click "Filter By Step"
    Then I should see an "Filter Checkboxes All At Once" element
