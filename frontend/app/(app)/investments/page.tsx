"use client"
import { useEffect, useState } from "react"
import api from "@/lib/api"
import { TrendingUp } from "lucide-react"

export default function InvestmentsPage() {
  const [investments, setInvestments] = useState<any[]>([])

  useEffect(() => {
    api.get("/investments").then(({ data }) => setInvestments(data.investments || [])).catch(() => {})
  }, [])

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Investments</h1>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {investments.map((inv: any) => (
          <div key={inv.id} className="p-6 rounded-xl bg-card border border-border space-y-3">
            <div className="flex items-center gap-2">
              <TrendingUp className="w-5 h-5 text-primary" />
              <span className="font-semibold">{inv.name}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-sm text-muted-foreground">Invested</span>
              <span className="font-medium">{inv.amount} {inv.currency}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-sm text-muted-foreground">Current Value</span>
              <span className="text-primary font-medium">{inv.current_value} {inv.currency}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-sm text-muted-foreground">Return Rate</span>
              <span>{inv.return_rate}%</span>
            </div>
          </div>
        ))}
        {investments.length === 0 && <p className="text-muted-foreground">No investments yet</p>}
      </div>
    </div>
  )
}
