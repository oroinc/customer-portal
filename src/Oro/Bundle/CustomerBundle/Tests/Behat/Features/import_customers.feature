Feature: Import Customers
    In order to add multiple customers at once
    As an Administrator
    I want to be able to import customers from a CSV file using a provided template

    Scenario: Import new Customer Users with ISO_8859_1 encoding
        Given I login as administrator
        And I go to Customers/Customers
        And I click "Import file"
        And I upload "import_customers/import_iso_8859_1.csv" file to "Customer Import File"
        And I click "Import file"
        And Email should contains the following "Errors: 0 processed: 1, read: 1, added: 1, updated: 0, replaced: 0" text
        And I reload the page
        And I should see following grid:
            | Name                        | Group | Account                     |
            | Associ? ? Nom d'utilisateur |       | Associ? ? Nom d'utilisateur |
