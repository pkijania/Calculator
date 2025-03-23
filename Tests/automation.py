from selenium.common.exceptions import NoSuchElementException
from selenium.webdriver.common.action_chains import ActionChains
from locators import *

class PositionValidator:
    @staticmethod
    def check_if_exists(driver, check):
        try:
            info = driver.find_element(*check).is_displayed()
            if info is True:
                return True
            else:
                return False
        except NoSuchElementException:
            return False

class TextValidator:
    @staticmethod
    def check_if_text_exists(driver, text):
        try:
            return text in driver.page_source
        except NoSuchElementException:
            return False

class Calculate:
    def __init__(self, driver):
        self.driver = driver

    def calculate_size(self, width, length, height):
        self.driver.find_element(*Other.coockies).click()
        self.driver.execute_script("arguments[0].value = {};".format(width), self.driver.find_element(*Size.width))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Size.width))
        self.driver.execute_script("arguments[0].value = {};".format(length), self.driver.find_element(*Size.length))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Size.length))
        self.driver.execute_script("arguments[0].value = {};".format(height), self.driver.find_element(*Size.height))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Size.height))

    def calculate_content(self, number_of_people, number_of_agd_devices, instalation_length):
        self.driver.execute_script("arguments[0].value = {};".format(number_of_people), self.driver.find_element(*Content.number_of_people))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Content.number_of_people))
        self.driver.execute_script("arguments[0].value = {};".format(number_of_agd_devices), self.driver.find_element(*Content.number_of_agd_devices))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Content.number_of_agd_devices))
        self.driver.execute_script("arguments[0].value = {};".format(instalation_length), self.driver.find_element(*Content.instalation_length))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Content.instalation_length))

    def calculate_brands_and_layout(self):
        ActionChains(self.driver).move_to_element(self.driver.find_element(*Brands.gree_device)).click().perform()
        ActionChains(self.driver).move_to_element(self.driver.find_element(*Brands.lg_device)).click().perform()

        ActionChains(self.driver).move_to_element(self.driver.find_element(*Layout.attic)).click().perform()
        ActionChains(self.driver).move_to_element(self.driver.find_element(*Layout.position_of_window)).click().perform()

    def calculate_intalation(self, concrete_length):
        ActionChains(self.driver).move_to_element(self.driver.find_element(*Instalation.use_concrete)).click().perform()
        self.driver.execute_script("arguments[0].value = {};".format(concrete_length), self.driver.find_element(*Instalation.concrete_length))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Instalation.concrete_length))
        ActionChains(self.driver).move_to_element(self.driver.find_element(*Other.submit)).click().perform()

    def calculate_exists(self):
        return PositionValidator.check_if_exists(self.driver, Other.coockies)

class Credentials:
    def __init__(self, driver):
        self.driver = driver

    def credentials(self, name, email, telephone, location):
        self.driver.find_element(*Details.name).send_keys(name)
        self.driver.find_element(*Details.email).send_keys(email)
        self.driver.find_element(*Details.telephone).send_keys(telephone)
        self.driver.find_element(*Details.location).send_keys(location)
        ActionChains(self.driver).move_to_element(self.driver.find_element(*Details.send)).click().perform()

    def credentials_exists(self):
        return PositionValidator.check_if_exists(self.driver, Details.send)

    def credentials_text_exists(self, expected_text):
        return TextValidator.check_if_text_exists(self.driver, expected_text)
