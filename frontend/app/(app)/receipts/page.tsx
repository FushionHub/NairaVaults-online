"use client"
import { useEffect, useState } from "react"
import api from "@/lib/api"
import Link from "next/link"
import { FileText } from "lucide-react"

export default function ReceiptsPage() {
  const [receipts, setReceipts] = useState<any[]>([])

  useEffect(() => {
    api.get("/receipts").then(({ data }) => setReceipts(data.receipts || [])).catch(() => {})
  }, [])

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Receipts</h1>
        <Link href="/statements" className="px-4 py-2 rounded-lg bg-accent text-sm">Statements</Link>
      </div>
      <div className="space-y-3">
        {receipts.map((r: any) => (
          <Link key={r.id} href={`/receipts/${r.id}`}
            className="block p-4 rounded-lg bg-card border border-border hover:border-primary transition">
            <div className="flex items-center gap-3">
              <FileText className="w-5 h-5 text-primary" />
              <div>
                <p className="font-medium">{r.type} - {r.amount} {r.currency}</p>
                <p className="text-sm text-muted-foreground">{new Date(r.created_at).toLocaleDateString()}</p>
              </div>
            </div>
          </Link>
        ))}
      </div>
    </div>
  )
}
