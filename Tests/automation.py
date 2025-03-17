from selenium.webdriver.common.by import By
from selenium.common.exceptions import NoSuchElementException
from selenium.webdriver.common.action_chains import ActionChains

class ParametersLocators:
    coockies = (By.ID, "sfAgreeAllButton")

    width = (By.NAME, "width")
    length = (By.NAME, "length")
    height = (By.NAME, "height")

    number_of_people = (By.NAME, "number_of_people")
    number_of_agd_devices = (By.NAME, "number_of_agd_devices")
    instalation_length = (By.NAME, "instalation_length")

    gree_device = (By.NAME, "gree_device")
    lg_device = (By.NAME, "lg_device")
    panasonic_device = (By.NAME, "panasonic_device")

    attic = (By.NAME, "attic")
    position_of_window = (By.NAME, "position_of_window")

    use_trough = (By.ID, "use_trough")
    trough_length = (By.ID, "trough_length")
    use_concrete = (By.ID, "use_concrete")
    concrete_length = (By.ID, "concrete_length")
    use_bricks = (By.ID, "use_bricks")
    brick_length = (By.ID, "brick_length")

    submit = (By.NAME, "cf-count")

class CredentialsLocators:
    name = (By.NAME, "cf-name")
    email = (By.NAME, "cf-email")
    telephone = (By.NAME, "cf-telephone")
    location = (By.NAME, "cf-location")
    message = (By.NAME, "cf-message")
    send = (By.NAME, "cf-submitted")

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

    def calculate(self):
        self.driver.find_element(*ParametersLocators.coockies).click()
        
        self.driver.execute_script("arguments[0].value = 5;", self.driver.find_element(*ParametersLocators.width))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*ParametersLocators.width))
        self.driver.execute_script("arguments[0].value = 5;", self.driver.find_element(*ParametersLocators.length))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*ParametersLocators.length))
        self.driver.execute_script("arguments[0].value = 2;", self.driver.find_element(*ParametersLocators.height))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*ParametersLocators.height))

        self.driver.execute_script("arguments[0].value = 2;", self.driver.find_element(*ParametersLocators.number_of_people))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*ParametersLocators.number_of_people))
        self.driver.execute_script("arguments[0].value = 3;", self.driver.find_element(*ParametersLocators.number_of_agd_devices))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*ParametersLocators.number_of_agd_devices))
        self.driver.execute_script("arguments[0].value = 5;", self.driver.find_element(*ParametersLocators.instalation_length))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*ParametersLocators.instalation_length))

        ActionChains(self.driver).move_to_element(self.driver.find_element(*ParametersLocators.gree_device)).click().perform()
        ActionChains(self.driver).move_to_element(self.driver.find_element(*ParametersLocators.lg_device)).click().perform()

        ActionChains(self.driver).move_to_element(self.driver.find_element(*ParametersLocators.lg_device)).click().perform()
        ActionChains(self.driver).move_to_element(self.driver.find_element(*ParametersLocators.lg_device)).click().perform()

        ActionChains(self.driver).move_to_element(self.driver.find_element(*ParametersLocators.use_concrete)).click().perform()
        self.driver.execute_script("arguments[0].value = 4;", self.driver.find_element(*ParametersLocators.concrete_length))
        self.driver.execute_script("arguments[0].dispatchEvent(new Event('change'));", self.driver.find_element(*ParametersLocators.concrete_length))

        ActionChains(self.driver).move_to_element(self.driver.find_element(*ParametersLocators.submit)).click().perform()

    def calculate_exists(self):
        return PositionValidator.check_if_exists(self.driver, ParametersLocators.coockies)

class Credentials:
    def __init__(self, driver):
        self.driver = driver

    def credentials(self, name, email, telephone, location):
        self.driver.find_element(*CredentialsLocators.name).send_keys(name)
        self.driver.find_element(*CredentialsLocators.email).send_keys(email)
        self.driver.find_element(*CredentialsLocators.telephone).send_keys(telephone)
        self.driver.find_element(*CredentialsLocators.location).send_keys(location)
        ActionChains(self.driver).move_to_element(self.driver.find_element(*CredentialsLocators.send)).click().perform()

    def credentials_exists(self):
        return PositionValidator.check_if_exists(self.driver, CredentialsLocators.send)

    def credentials_text_exists(self, expected_text):
        return TextValidator.check_if_text_exists(self.driver, expected_text)
