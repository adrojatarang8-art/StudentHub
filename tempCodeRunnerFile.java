import java.lang.annotation.*;
import java.lang.reflect.Field;
import java.util.ArrayList;
import java.util.List;

// Annotation 1
@Retention(RetentionPolicy.RUNTIME)
@Target(ElementType.FIELD)
@interface NotBlank {
}

// Annotation 2
@Retention(RetentionPolicy.RUNTIME)
@Target(ElementType.FIELD)
@interface MaxLength {
    int value();
}

// Signup Form
class SignupForm {

    @NotBlank
    @MaxLength(15)
    String username;

    @NotBlank
    @MaxLength(30)
    String email;

    @NotBlank
    @MaxLength(10)
    String password;

    SignupForm(String username, String email, String password) {
        this.username = username;
        this.email = email;
        this.password = password;
    }
}

// Validator
class Validator {

    public static List<String> validate(Object obj) {

        List<String> errors = new ArrayList<>();

        Field[] fields = obj.getClass().getDeclaredFields();

        for (Field field : fields) {

            try {
                field.setAccessible(true);

                Object value = field.get(obj);

                // Check NotBlank
                if (field.isAnnotationPresent(NotBlank.class)) {

                    if (value == null ||
                            value.toString().trim().isEmpty()) {

                        errors.add(field.getName()
                                + " cannot be blank");
                    }
                }

                // Check MaxLength
                if (field.isAnnotationPresent(MaxLength.class)
                        && value != null) {

                    MaxLength max =
                            field.getAnnotation(MaxLength.class);

                    if (value.toString().length() > max.value()) {

                        errors.add(field.getName()
                                + " cannot exceed "
                                + max.value()
                                + " characters");
                    }
                }

            } catch (IllegalAccessException e) {
                System.out.println("Error reading field.");
            }
        }

        return errors;
    }
}

public class FormValidator {

    // Method to display validation
    static void checkForm(int number, SignupForm form) {

        List<String> errors = Validator.validate(form);

        System.out.println("\n==================================");
        System.out.println("        FORM " + number + " VALIDATION");
        System.out.println("==================================");

        System.out.println("Username : "
                + (form.username.isEmpty()
                ? "[Blank]" : form.username));

        System.out.println("Email    : "
                + (form.email.isEmpty()
                ? "[Blank]" : form.email));

        System.out.println("Password : "
                + (form.password.isEmpty()
                ? "[Blank]" : form.password));

        System.out.println("----------------------------------");

        if (errors.isEmpty()) {

            System.out.println("Status : VALID");
            System.out.println("No validation errors found.");

        } else {

            System.out.println("Status : INVALID");
            System.out.println("Total Errors : " + errors.size());

            System.out.println("Validation Errors:");

            int count = 1;

            for (String error : errors) {
                System.out.println(count + ". " + error);
                count++;
            }
        }
    }

    public static void main(String[] args) {

        // Example 1 - Blank username
        SignupForm form1 = new SignupForm(
                "",
                "tarang@gmail.com",
                "12345678"
        );

        // Example 2 - Too long email and password
        SignupForm form2 = new SignupForm(
                "Tarang",
                "tarangadrojaemail123456789@gmail.com",
                "1234567891011"
        );

        // Example 3 - Correct form
        SignupForm form3 = new SignupForm(
                "Tarang",
                "tarang@gmail.com",
                "abc12345"
        );

        checkForm(1, form1);
        checkForm(2, form2);
        checkForm(3, form3);
    }
}
