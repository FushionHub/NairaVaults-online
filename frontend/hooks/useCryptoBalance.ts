import { useEffect, useState } from "react"
import api from "@/lib/api"

interface WalletBalance {
  id: number
  coin_symbol: string
  balance: string
  public_address: string
  current_price?: string
  value_usd?: string
}

export function useCryptoBalance(pollIntervalMs = 30000) {
  const [wallets, setWallets] = useState<WalletBalance[]>([])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    let cancelled = false

    const fetchBalances = async () => {
      try {
        const { data } = await api.get("/crypto/wallets")
        if (!cancelled) {
          setWallets(data.wallets || [])
          setIsLoading(false)
        }
      } catch {
        if (!cancelled) setIsLoading(false)
      }
    }

    fetchBalances()
    const interval = setInterval(fetchBalances, pollIntervalMs)

    return () => {
      cancelled = true
      clearInterval(interval)
    }
  }, [pollIntervalMs])

  return { wallets, isLoading }
}
