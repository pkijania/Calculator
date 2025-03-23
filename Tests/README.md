# Selenium testing
Repository consists of automatic tests

### Commands to run the tests
- Run the test:
```
python -m behave
```
- Process the test results and save an HTML report into the allure-report directory:
```
allure generate
```
- View the report:
```
allure open
```
- Put the test results into a temporary directory and then automatically open the main page of the report in a web browser:
```
allure serve
```
