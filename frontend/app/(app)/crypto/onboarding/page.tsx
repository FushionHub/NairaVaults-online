"use client"
import { useState } from "react"
import { useRouter } from "next/navigation"
import api from "@/lib/api"
import { Wallet, Shield, ArrowRight } from "lucide-react"

export default function CryptoOnboardingPage() {
  const router = useRouter()
  const [isCreating, setIsCreating] = useState(false)
  const [selectedCoins, setSelectedCoins] = useState<string[]>(["BTC", "ETH"])

  const coins = [
    { symbol: "BTC", name: "Bitcoin" },
    { symbol: "ETH", name: "Ethereum" },
    { symbol: "BNB", name: "BNB" },
    { symbol: "USDT", name: "Tether" },
    { symbol: "SOL", name: "Solana" },
    { symbol: "MATIC", name: "Polygon" },
  ]

  const toggleCoin = (symbol: string) => {
    setSelectedCoins((prev) => prev.includes(symbol) ? prev.filter((c) => c !== symbol) : [...prev, symbol])
  }

  const handleCreate = async () => {
    setIsCreating(true)
    try {
      for (const symbol of selectedCoins) {
        await api.post("/crypto/wallets", { coin_symbol: symbol })
      }
      router.push("/dashboard")
    } finally {
      setIsCreating(false)
    }
  }

  return (
    <div className="max-w-lg mx-auto space-y-8 text-center">
      <Wallet className="w-16 h-16 mx-auto text-primary" />
      <h1 className="text-2xl font-bold">Set Up Your Crypto Wallets</h1>
      <p className="text-muted-foreground">Select the coins you want to start with. You can add more later.</p>
      <div className="grid grid-cols-2 gap-3">
        {coins.map((coin) => (
          <button key={coin.symbol} onClick={() => toggleCoin(coin.symbol)}
            className={`p-4 rounded-lg border text-left transition ${selectedCoins.includes(coin.symbol) ? "border-primary bg-primary/10" : "border-input"}`}>
            <p className="font-semibold">{coin.symbol}</p>
            <p className="text-sm text-muted-foreground">{coin.name}</p>
          </button>
        ))}
      </div>
      <div className="p-4 rounded-lg bg-accent flex items-center gap-2 text-sm text-muted-foreground">
        <Shield className="w-4 h-4 text-primary" />
        <span>Wallets are secured with Privy embedded wallet infrastructure</span>
      </div>
      <button onClick={handleCreate} disabled={isCreating || selectedCoins.length === 0}
        className="w-full py-3 rounded-lg bg-primary text-primary-foreground font-medium disabled:opacity-50 flex items-center justify-center gap-2">
        {isCreating ? "Creating Wallets..." : <><span>Create Wallets</span><ArrowRight className="w-4 h-4" /></>}
      </button>
    </div>
  )
}
