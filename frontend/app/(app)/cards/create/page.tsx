"use client"
import { useState } from "react"
import { useRouter } from "next/navigation"
import api from "@/lib/api"

export default function CreateCardPage() {
  const router = useRouter()
  const [cardType, setCardType] = useState("visa")
  const [currency, setCurrency] = useState("USD")
  const [fundingAmount, setFundingAmount] = useState("")
  const [isLoading, setIsLoading] = useState(false)

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/cards", { card_type: cardType, currency, initial_funding: fundingAmount })
      router.push("/cards")
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="max-w-lg mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Create Virtual Card</h1>
      <form onSubmit={handleCreate} className="space-y-4">
        <div>
          <label className="block text-sm font-medium mb-1">Card Type</label>
          <div className="grid grid-cols-3 gap-2">
            {["visa", "verve", "mastercard"].map((t) => (
              <button key={t} type="button" onClick={() => setCardType(t)}
                className={`p-3 rounded-lg border capitalize text-sm transition ${cardType === t ? "border-primary bg-primary/10" : "border-input"}`}>
                {t}
              </button>
            ))}
          </div>
        </div>
        <select value={currency} onChange={(e) => setCurrency(e.target.value)}
          className="w-full px-4 py-2 rounded-lg border border-input bg-background">
          <option value="USD">USD</option>
          <option value="NGN">NGN</option>
        </select>
        <div>
          <label className="block text-sm font-medium mb-1">Initial Funding</label>
          <input type="number" value={fundingAmount} onChange={(e) => setFundingAmount(e.target.value)} min="1"
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
        </div>
        <button type="submit" disabled={isLoading}
          className="w-full py-3 rounded-lg bg-primary text-primary-foreground font-medium disabled:opacity-50">
          {isLoading ? "Creating..." : "Create Card"}
        </button>
      </form>
    </div>
  )
}
