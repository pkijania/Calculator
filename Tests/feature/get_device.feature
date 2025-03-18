Feature: Calculate_default
    Test which calculates power using default parameters

Scenario: Calculate
    Given I am situated on the calculator subpage
    When I configure all the parameters: 5, 5, 2, 2, 3, 5, 4 and click calculate button
    Then I proceed to credentials subpage

Scenario: Credentials
    Given I am situated on the credentials subpage
    When I provide my credentials: John Smith, 3lastik@gmail.com, 123 123 123, Los Angeles and click send button
    Then I proceed to summary subpage and see Szczegóły dotyczące wybranych klimatyzatorów:
