import { describe, it, expect, beforeEach, vi } from "vitest"

vi.mock("axios", () => ({
  default: {
    create: () => ({
      interceptors: {
        request: { use: vi.fn() },
        response: { use: vi.fn() },
      },
      get: vi.fn(),
      post: vi.fn(),
    }),
  },
}))

vi.mock("@/lib/api", () => ({
  default: {
    post: vi.fn(),
    get: vi.fn(),
    interceptors: {
      request: { use: vi.fn() },
      response: { use: vi.fn() },
    },
  },
}))

describe("Auth Store", () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it("should have initial state with no user", async () => {
    const { useAuthStore } = await import("@/store/authStore")
    const state = useAuthStore.getState()
    expect(state.user).toBeNull()
    expect(state.token).toBeNull()
    expect(state.isLoading).toBe(false)
  })

  it("should track 2FA requirement state", async () => {
    const { useAuthStore } = await import("@/store/authStore")
    const state = useAuthStore.getState()
    expect(state.requires2fa).toBe(false)
  })
})
