from selenium.common.exceptions import NoSuchElementException
from selenium.webdriver.common.action_chains import ActionChains
from locators import *
import json
import time

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

    @staticmethod
    def check_if_text_exists(driver, text):
        try:
            return text in driver.page_source
        except NoSuchElementException:
            return False

class LoadConfig:
    @staticmethod
    def load_config():
        with open("config.json", "r", encoding="utf-8") as file:
            return json.load(file)

class Calculate:
    def __init__(self, driver):
        self.driver = driver
        self.config_data = LoadConfig.load_config()
        self.width = self.config_data["width"]
        self.length = self.config_data["length"]
        self.height = self.config_data["height"]
        self.number_of_people = self.config_data["number_of_people"]
        self.number_of_agd_devices = self.config_data["number_of_agd_devices"]
        self.instalation_length = self.config_data["instalation_length"]
        self.concrete_length = self.config_data["concrete_length"]

    def calculate_size(self):
        self.driver.find_element(*Other.coockies).click()
        self.driver.execute_script("arguments[0].value = {};".format(self.width), self.driver.find_element(*Size.width))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Size.width))
        self.driver.execute_script("arguments[0].value = {};".format(self.length), self.driver.find_element(*Size.length))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Size.length))
        self.driver.execute_script("arguments[0].value = {};".format(self.height), self.driver.find_element(*Size.height))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Size.height))

    def calculate_content(self):
        self.driver.execute_script("arguments[0].value = {};".format(self.number_of_people), self.driver.find_element(*Content.number_of_people))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Content.number_of_people))
        self.driver.execute_script("arguments[0].value = {};".format(self.number_of_agd_devices), self.driver.find_element(*Content.number_of_agd_devices))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Content.number_of_agd_devices))
        self.driver.execute_script("arguments[0].value = {};".format(self.instalation_length), self.driver.find_element(*Content.instalation_length))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Content.instalation_length))

    def calculate_brands_and_layout(self):
        ActionChains(self.driver).move_to_element(self.driver.find_element(*Brands.gree_device)).click().perform()
        ActionChains(self.driver).move_to_element(self.driver.find_element(*Brands.lg_device)).click().perform()

        ActionChains(self.driver).move_to_element(self.driver.find_element(*Layout.attic)).click().perform()
        ActionChains(self.driver).move_to_element(self.driver.find_element(*Layout.position_of_window)).click().perform()

    def calculate_intalation(self):
        ActionChains(self.driver).move_to_element(self.driver.find_element(*Instalation.use_concrete)).click().perform()
        self.driver.execute_script("arguments[0].value = {};".format(self.concrete_length), self.driver.find_element(*Instalation.concrete_length))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*Instalation.concrete_length))
        ActionChains(self.driver).move_to_element(self.driver.find_element(*Other.submit)).click().perform()

    def calculate_exists(self):
        return PositionValidator.check_if_exists(self.driver, Other.coockies)

class Credentials:
    def __init__(self, driver):
        self.driver = driver
        self.config_data = LoadConfig.load_config()
        self.name = self.config_data["name"]
        self.email = self.config_data["email"]
        self.telephone = self.config_data["telephone"]
        self.location = self.config_data["location"]
        self.expected_text = self.config_data["expected_text"]

    def credentials(self):
        self.driver.find_element(*Details.name).send_keys(self.name)
        self.driver.find_element(*Details.email).send_keys(self.email)
        self.driver.find_element(*Details.telephone).send_keys(self.telephone)
        self.driver.find_element(*Details.location).send_keys(self.location)
        ActionChains(self.driver).move_to_element(self.driver.find_element(*Details.send)).click().perform()

    def credentials_exists(self):
        return PositionValidator.check_if_exists(self.driver, Details.send)

    def credentials_text_exists(self):
        return PositionValidator.check_if_text_exists(self.driver, self.expected_text)
