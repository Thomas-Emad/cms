
/**
 * Shared setup notes for Phase 3 tests (not a file Laravel loads itself -
 * documenting the pattern used identically in each test class below,
 * since there's no custom base TestCase in this checkpoint).
 *
 * Every test:
 *   1. Creates a Hotel directly (bypassing HotelSeeder for test isolation).
 *   2. Creates a hotel_admin User with hotel_id = that hotel.
 *   3. Acts as that user OR explicitly sets CurrentHotel, depending on
 *      whether the test is exercising an authenticated admin action or an
 *      unauthenticated guest request (which resolves the single-tenant
 *      fallback per CurrentHotel::get()'s documented resolution order).
 *   4. For multi-tenant isolation tests, a SECOND Hotel + User pair is
 *      created to prove cross-tenant access is blocked.
 */
