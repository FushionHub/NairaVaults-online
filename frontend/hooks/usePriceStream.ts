import { useEffect, useState, useRef } from "react"

interface PriceData {
  symbol: string
  price: string
  change24h?: string
}

export function usePriceStream(symbols: string[]) {
  const [prices, setPrices] = useState<Record<string, PriceData>>({})
  const [isConnected, setIsConnected] = useState(false)
  const eventSourceRef = useRef<EventSource | null>(null)

  useEffect(() => {
    if (symbols.length === 0) return

    const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api/v1"
    const url = `${baseUrl}/market/prices/stream?symbols=${symbols.join(",")}`

    const es = new EventSource(url)
    eventSourceRef.current = es

    es.onopen = () => setIsConnected(true)

    es.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data)
        setPrices((prev) => ({
          ...prev,
          [data.symbol]: {
            symbol: data.symbol,
            price: data.price,
            change24h: data.change_24h,
          },
        }))
      } catch {
        // skip malformed data
      }
    }

    es.onerror = () => {
      setIsConnected(false)
      es.close()
      setTimeout(() => {
        eventSourceRef.current = new EventSource(url)
      }, 5000)
    }

    return () => {
      es.close()
      eventSourceRef.current = null
    }
  }, [symbols.join(",")])

  return { prices, isConnected }
}
