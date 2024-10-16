# Refactor Task

## Thoughts on the Original Code
The original code was functional but had several areas where the separation of concerns was not clearly maintained. Business logic was mixed with the controller logic, making the code harder to maintain and test.

### Issues:
- Business logic cluttered the controller.
- Methods were too large and complex.
- Lack of adherence to SOLID principles.

## Refactoring Changes
I refactored the code by:
1. Moving all business logic to the `BookingRepository`.
2. Ensuring that the `BookingController` was more lightweight and only handled routing and request data.
3. Breaking down large methods into smaller, reusable functions.
4. Improved code readability and structure for future maintainability.

## How I would do it differently (optional)
Given more time, I would have added more unit tests and implemented dependency injection more thoroughly.
