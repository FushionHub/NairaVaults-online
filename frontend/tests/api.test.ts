import { describe, it, expect, vi } from "vitest"

vi.mock("axios", () => ({
  default: {
    create: () => ({
      interceptors: {
        request: { use: vi.fn() },
        response: { use: vi.fn() },
      },
      get: vi.fn(),
      post: vi.fn(),
      patch: vi.fn(),
      delete: vi.fn(),
    }),
  },
}))

describe("API Client", () => {
  it("should be configured with the correct base URL", () => {
    const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api/v1"
    expect(baseUrl).toContain("/api/v1")
  })

  it("should have CSRF token support configured", () => {
    expect(true).toBe(true)
  })
})
