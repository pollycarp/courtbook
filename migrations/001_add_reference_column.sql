-- Migration 001: Add booking reference column
ALTER TABLE bookings
    ADD COLUMN reference VARCHAR(20) NULL UNIQUE AFTER time_slot;
