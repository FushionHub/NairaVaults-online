import { describe, it, expect, vi } from "vitest"
import { renderHook } from "@testing-library/react"

class MockEventSource {
  onmessage: ((event: any) => void) | null = null
  onopen: (() => void) | null = null
  onerror: (() => void) | null = null
  close = vi.fn()

  constructor(public url: string) {
    setTimeout(() => this.onopen?.(), 0)
  }
}

vi.stubGlobal("EventSource", MockEventSource)

describe("usePriceStream", () => {
  it("should initialize with empty prices", async () => {
    const { usePriceStream } = await import("@/hooks/usePriceStream")
    const { result } = renderHook(() => usePriceStream(["BTCUSDT"]))

    expect(result.current.prices).toEqual({})
  })

  it("should start disconnected", async () => {
    const { usePriceStream } = await import("@/hooks/usePriceStream")
    const { result } = renderHook(() => usePriceStream(["BTCUSDT"]))

    expect(result.current.isConnected).toBe(false)
  })

  it("should not connect with empty symbols", async () => {
    const { usePriceStream } = await import("@/hooks/usePriceStream")
    const { result } = renderHook(() => usePriceStream([]))

    expect(result.current.prices).toEqual({})
    expect(result.current.isConnected).toBe(false)
  })
})
