from selenium.webdriver.common.by import By
from automation import *
from behave import *

#1
@given("I am situated on the calculator subpage")
def step_impl(context):
    calculate = Calculate(context.driver)
    assert calculate.calculate_exists()

@when("I click calculate button")
def step_impl(context):
    context.driver.find_element(By.ID, "sfAgreeAllButton").click()

    context.driver.execute_script("arguments[0].value = 5;", context.driver.find_element(By.NAME, "width"))
    context.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", context.driver.find_element(By.NAME, "width"))
    context.driver.execute_script("arguments[0].value = 5;", context.driver.find_element(By.NAME, "length"))
    context.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", context.driver.find_element(By.NAME, "length"))
    context.driver.execute_script("arguments[0].value = 2;", context.driver.find_element(By.NAME, "height"))
    context.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", context.driver.find_element(By.NAME, "height"))

    context.driver.execute_script("arguments[0].value = 2;", context.driver.find_element(By.NAME, "number_of_people"))
    context.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", context.driver.find_element(By.NAME, "number_of_people"))
    context.driver.execute_script("arguments[0].value = 3;", context.driver.find_element(By.NAME, "number_of_agd_devices"))
    context.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", context.driver.find_element(By.NAME, "number_of_agd_devices"))
    context.driver.execute_script("arguments[0].value = 5;", context.driver.find_element(By.NAME, "instalation_length"))
    context.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", context.driver.find_element(By.NAME, "instalation_length"))

    ActionChains(context.driver).move_to_element(context.driver.find_element(By.NAME, "gree_device")).click().perform()
    ActionChains(context.driver).move_to_element(context.driver.find_element(By.NAME, "lg_device")).click().perform()

    ActionChains(context.driver).move_to_element(context.driver.find_element(By.NAME, "attic")).click().perform()
    ActionChains(context.driver).move_to_element(context.driver.find_element(By.NAME, "position_of_window")).click().perform()

    ActionChains(context.driver).move_to_element(context.driver.find_element(By.ID, "use_concrete")).click().perform()
    context.driver.execute_script("arguments[0].value = 4;", context.driver.find_element(By.ID, "concrete_length"))
    context.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", context.driver.find_element(By.ID, "concrete_length"))

    ActionChains(context.driver).move_to_element(context.driver.find_element(By.NAME, "cf-count")).click().perform()

@then("I proceed to credentials subpage")
def step_impl(context):
    credentials = Credentials(context.driver)
    assert credentials.credentials_exists()

#2
@given("I am situated on the credentials subpage")
def step_impl(context):
    credentials = Credentials(context.driver)
    assert credentials.credentials_exists()

@when("I provide my credentials: {name}, {email}, {telephone}, {location} and click send button")
def step_impl(context, name, email, telephone, location):
    context.driver.find_element(By.NAME, "cf-name").send_keys(name)
    context.driver.find_element(By.NAME, "cf-email").send_keys(email)
    context.driver.find_element(By.NAME, "cf-telephone").send_keys(telephone)
    context.driver.find_element(By.NAME, "cf-location").send_keys(location)
    ActionChains(context.driver).move_to_element(context.driver.find_element(By.NAME, "cf-submitted")).click().perform()

@then("I proceed to summary subpage and see {expected_text}")
def step_impl(context, expected_text):
    credentials = Credentials(context.driver)
    assert credentials.credentials_text_exists(expected_text), f"Didn't find '{expected_text}' on the subpage!"
