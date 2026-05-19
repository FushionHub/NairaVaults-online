"use client"

import { useEffect, useState } from "react"
import Link from "next/link"
import { motion } from "framer-motion"
import {
  Wallet, ArrowLeftRight, Shield, CreditCard, BarChart3,
  Bot, Mic, TrendingUp, PiggyBank, Users, Zap, Lock,
} from "lucide-react"

const headlines = ["Bank Smarter", "Trade Faster", "Earn More"]

const features = [
  { icon: Wallet, title: "Multi-Currency Banking", desc: "Hold NGN, USD, EUR, GBP, GHS with dedicated virtual accounts" },
  { icon: ArrowLeftRight, title: "Crypto Trading", desc: "Buy, sell, swap 50+ cryptocurrencies with live Binance prices" },
  { icon: CreditCard, title: "Virtual Cards", desc: "Visa, Mastercard, Verve cards for online payments" },
  { icon: PiggyBank, title: "Savings & Investments", desc: "Lock funds and earn competitive interest rates" },
  { icon: BarChart3, title: "Binary Trading", desc: "Predict market movements with real-time charts" },
  { icon: Bot, title: "AI Financial Assistant", desc: "Powered by Gemini and Grok for intelligent insights" },
]

export default function LandingPage() {
  const [headlineIndex, setHeadlineIndex] = useState(0)

  useEffect(() => {
    const interval = setInterval(() => {
      setHeadlineIndex((prev) => (prev + 1) % headlines.length)
    }, 3000)
    return () => clearInterval(interval)
  }, [])

  return (
    <div className="min-h-screen bg-background">
      {/* Navigation */}
      <nav className="sticky top-0 z-50 bg-background/80 backdrop-blur border-b border-border">
        <div className="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
          <span className="text-2xl font-bold text-primary">NairaVault</span>
          <div className="hidden md:flex items-center gap-6 text-sm">
            <a href="#features" className="text-muted-foreground hover:text-foreground">Features</a>
            <a href="#security" className="text-muted-foreground hover:text-foreground">Security</a>
            <a href="#ai" className="text-muted-foreground hover:text-foreground">AI</a>
          </div>
          <div className="flex items-center gap-3">
            <Link href="/sign-in" className="px-4 py-2 text-sm hover:text-primary transition">Sign In</Link>
            <Link href="/sign-up" className="px-4 py-2 text-sm rounded-lg bg-primary text-primary-foreground hover:opacity-90 transition">
              Get Started
            </Link>
          </div>
        </div>
      </nav>

      {/* Hero */}
      <section className="relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/10" />
        <div className="max-w-7xl mx-auto px-6 py-24 md:py-36 text-center relative">
          <motion.h1
            key={headlineIndex}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -20 }}
            className="text-5xl md:text-7xl font-bold mb-6"
          >
            <span className="text-primary">{headlines[headlineIndex]}</span>
          </motion.h1>
          <p className="text-xl text-muted-foreground max-w-2xl mx-auto mb-8">
            Nigeria&apos;s premier fintech platform. Bank, trade, invest, and grow your wealth — all in one place.
          </p>
          <div className="flex gap-4 justify-center">
            <Link href="/sign-up" className="px-8 py-4 rounded-lg bg-primary text-primary-foreground font-medium hover:opacity-90 transition text-lg">
              Create Free Account
            </Link>
            <a href="#features" className="px-8 py-4 rounded-lg border border-border font-medium hover:bg-accent transition text-lg">
              Learn More
            </a>
          </div>
        </div>
      </section>

      {/* Features */}
      <section id="features" className="py-24 bg-accent/30">
        <div className="max-w-7xl mx-auto px-6">
          <h2 className="text-3xl md:text-4xl font-bold text-center mb-16">Everything You Need</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {features.map((f, i) => (
              <motion.div
                key={i}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.1 }}
                className="p-8 rounded-xl bg-card border border-border hover:border-primary/50 transition space-y-4"
              >
                <f.icon className="w-10 h-10 text-primary" />
                <h3 className="text-xl font-semibold">{f.title}</h3>
                <p className="text-muted-foreground">{f.desc}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Security */}
      <section id="security" className="py-24">
        <div className="max-w-7xl mx-auto px-6 text-center">
          <h2 className="text-3xl md:text-4xl font-bold mb-16">Bank-Grade Security</h2>
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
            {[
              { icon: Shield, label: "KYC Verified" },
              { icon: Lock, label: "AES-256 Encryption" },
              { icon: Zap, label: "2FA Authentication" },
              { icon: Users, label: "Trusted Devices" },
            ].map((s, i) => (
              <div key={i} className="p-6 rounded-xl bg-card border border-border space-y-3 text-center">
                <s.icon className="w-8 h-8 mx-auto text-primary" />
                <p className="font-medium">{s.label}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* AI Section */}
      <section id="ai" className="py-24 bg-accent/30">
        <div className="max-w-7xl mx-auto px-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div className="space-y-6">
              <h2 className="text-3xl md:text-4xl font-bold">AI-Powered Finance</h2>
              <p className="text-muted-foreground text-lg">
                Get personalized financial insights powered by Google Gemini and xAI Grok.
                Voice-enabled with ElevenLabs natural speech synthesis.
              </p>
              <div className="flex gap-4">
                <div className="flex items-center gap-2 text-sm"><Bot className="w-5 h-5 text-primary" /> Chat Assistant</div>
                <div className="flex items-center gap-2 text-sm"><Mic className="w-5 h-5 text-primary" /> Voice Control</div>
                <div className="flex items-center gap-2 text-sm"><TrendingUp className="w-5 h-5 text-primary" /> Market Analysis</div>
              </div>
            </div>
            <div className="p-8 rounded-xl bg-card border border-border">
              <div className="space-y-4">
                <div className="flex gap-3">
                  <div className="w-8 h-8 rounded-full bg-accent flex items-center justify-center"><Users className="w-4 h-4" /></div>
                  <div className="p-3 rounded-lg bg-accent text-sm">What&apos;s the best time to buy BTC?</div>
                </div>
                <div className="flex gap-3">
                  <div className="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center"><Bot className="w-4 h-4 text-primary" /></div>
                  <div className="p-3 rounded-lg bg-primary/10 text-sm">
                    Based on historical patterns and current market sentiment, BTC tends to see lower volatility during weekends...
                    <br /><br />
                    <span className="text-xs text-muted-foreground italic">This is not regulated financial advice.</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="py-24">
        <div className="max-w-4xl mx-auto px-6 text-center space-y-8">
          <h2 className="text-3xl md:text-4xl font-bold">Ready to Get Started?</h2>
          <p className="text-xl text-muted-foreground">
            Join thousands of Nigerians managing their finances with NairaVault.
          </p>
          <Link href="/sign-up" className="inline-block px-8 py-4 rounded-lg bg-primary text-primary-foreground font-medium hover:opacity-90 transition text-lg">
            Create Your Free Account
          </Link>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-border py-12">
        <div className="max-w-7xl mx-auto px-6">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div>
              <h3 className="font-semibold mb-4">Product</h3>
              <ul className="space-y-2 text-sm text-muted-foreground">
                <li><a href="#features" className="hover:text-foreground">Features</a></li>
                <li><a href="#security" className="hover:text-foreground">Security</a></li>
                <li><a href="#ai" className="hover:text-foreground">AI</a></li>
              </ul>
            </div>
            <div>
              <h3 className="font-semibold mb-4">Company</h3>
              <ul className="space-y-2 text-sm text-muted-foreground">
                <li><a href="#" className="hover:text-foreground">About</a></li>
                <li><a href="#" className="hover:text-foreground">Careers</a></li>
                <li><a href="#" className="hover:text-foreground">Contact</a></li>
              </ul>
            </div>
            <div>
              <h3 className="font-semibold mb-4">Legal</h3>
              <ul className="space-y-2 text-sm text-muted-foreground">
                <li><a href="#" className="hover:text-foreground">Privacy</a></li>
                <li><a href="#" className="hover:text-foreground">Terms</a></li>
                <li><a href="#" className="hover:text-foreground">Compliance</a></li>
              </ul>
            </div>
            <div>
              <h3 className="font-semibold mb-4">Connect</h3>
              <ul className="space-y-2 text-sm text-muted-foreground">
                <li><a href="#" className="hover:text-foreground">Twitter</a></li>
                <li><a href="#" className="hover:text-foreground">LinkedIn</a></li>
                <li><a href="#" className="hover:text-foreground">WhatsApp</a></li>
              </ul>
            </div>
          </div>
          <div className="mt-12 pt-8 border-t border-border text-center text-sm text-muted-foreground">
            <p>&copy; {new Date().getFullYear()} NairaVault. All rights reserved.</p>
          </div>
        </div>
      </footer>
    </div>
  )
}
