@ticket-BAP-16988
@skip
# This test should be fixed and un-skipped after OPI-693 is implemented.
# In order to pass this test, you need:
#  - Configure your web server to handle www subdomain for the application
#  - Configure Application URL, Website URL and Website Secure URL to domain without www
Feature: Check correct redirect domain with and without www
  In order to check redirect domain with and without www
  As a User
  I want to have the ability to get url with correct prefix "www"

  Scenario: Check ability
    Given I am on the homepage
    And visit path "/product" on www subdomain
    Then check path "/product" is located on the base domain
    And visit path "/not-exists-path" on www subdomain
    Then check path "/not-exists-path" is located on the base domain
