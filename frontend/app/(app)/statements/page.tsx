"use client"
import { useState } from "react"
import api from "@/lib/api"

export default function StatementsPage() {
  const [dateFrom, setDateFrom] = useState("")
  const [dateTo, setDateTo] = useState("")
  const [accountType, setAccountType] = useState("fiat")
  const [isLoading, setIsLoading] = useState(false)

  const handleGenerate = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      const { data } = await api.post("/statements/generate", { date_from: dateFrom, date_to: dateTo, account_type: accountType, format: "pdf" })
      if (data.download_url) window.open(data.download_url)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="max-w-lg mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Generate Statement</h1>
      <form onSubmit={handleGenerate} className="space-y-4">
        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium mb-1">From</label>
            <input type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)}
              className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">To</label>
            <input type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)}
              className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          </div>
        </div>
        <select value={accountType} onChange={(e) => setAccountType(e.target.value)}
          className="w-full px-4 py-2 rounded-lg border border-input bg-background">
          <option value="fiat">Fiat</option>
          <option value="crypto">Crypto</option>
          <option value="combined">Combined</option>
        </select>
        <button type="submit" disabled={isLoading}
          className="w-full py-3 rounded-lg bg-primary text-primary-foreground font-medium disabled:opacity-50">
          {isLoading ? "Generating..." : "Generate Statement"}
        </button>
      </form>
    </div>
  )
}
