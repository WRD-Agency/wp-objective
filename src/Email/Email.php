<?php
/**
 * Contains the Email class.
 *
 * @package wrd\wp-object
 */

namespace Wrd\WpObjective\Email;

/**
 * Class for building & sending emails.
 */
class Email {
	/**
	 * The email's recipients.
	 *
	 * @var string[] $to
	 */
	private array $to = array();

	/**
	 * The email's CC recipients.
	 *
	 * @var string[] $cc
	 */
	private array $cc = array();

	/**
	 * The email's subject.
	 *
	 * @var string $subject
	 */
	private string $subject = '';

	/**
	 * The email's sender.
	 *
	 * @var ?string $from
	 */
	private ?string $from = null;

	/**
	 * The email's contents.
	 *
	 * @var string $body
	 */
	private string $body = '';

	/**
	 * Create a new email.
	 *
	 * @return self
	 */
	public static function build(): self {
		return new self();
	}

	/**
	 * Set the recipients.
	 *
	 * @param string|string[] $to The new to.
	 *
	 * @return self
	 */
	public function to( string|array $to ): self {
		if ( ! is_array( $to ) ) {
			$to = array( $to );
		}

		$this->to = $to;
		return $this;
	}

	/**
	 * Set the CC recipients.
	 *
	 * @param string|string[] $cc The new cc.
	 *
	 * @return self
	 */
	public function cc( string|array $cc ): self {
		if ( ! is_array( $cc ) ) {
			$cc = array( $cc );
		}

		$this->cc = $cc;
		return $this;
	}

	/**
	 * Set the subject.
	 *
	 * @param string $subject The new subject.
	 *
	 * @return self
	 */
	public function subject( string $subject ): self {
		$this->subject = $subject;
		return $this;
	}

	/**
	 * Set the from.
	 *
	 * @param string $from The new sender.
	 *
	 * @return self
	 */
	public function from( string $from ): self {
		$this->from = $from;
		return $this;
	}

	/**
	 * Add a heading to the email body.
	 *
	 * @param int    $level The heading level. 1-4 are supported.
	 *
	 * @param string $text The content of the heading.
	 *
	 * @return self
	 */
	public function heading( $level = 1, $text = '' ): self {
		if ( ! $text ) {
			$text = get_the_title();
		}

		$styles = array(
			1 => 'font-size: 35px; font-weight: 500;',
			2 => 'font-size: 28px; font-weight: 500;',
			3 => 'font-size: 22px; font-weight: 500;',
			4 => 'font-size: 18px; font-weight: 500;',
		);

		$tag = 'h' . intval( $level );

		$this->body .= '<' . esc_attr( $tag ) . ' style="' . esc_attr( $styles[ $level ] ) . ' display: block; margin-bottom: 2rem;">' . esc_html( $text ) . '</' . esc_attr( $tag ) . '>';

		return $this;
	}

	/**
	 * Add a paragraph to the email body.
	 *
	 * @param string $text The content of the paragraph.
	 *
	 * @return self
	 */
	public function paragraph( $text ) {
		$this->body .= '<p style="display: block; margin: 1rem 0;">' . esc_html( $text ) . '</p>';

		return $this;
	}

	/**
	 * Add a button to the email body.
	 *
	 * @param string $text The content of the button.
	 *
	 * @param string $url The URL of the button.
	 *
	 * @param string $campaign Optional. UTM Campaign to set on the link.
	 *
	 * @return self
	 */
	public function button( $text, $url, $campaign = '-' ) {
		$url = add_query_arg(
			array(
				'utm_source'   => 'courser',
				'utm_medium'   => 'email',
				'utm_campaign' => $campaign,
			),
			$url
		);

		$this->body .= '
		<a target="_blank" href="' . esc_attr( $url ) . '" style="background-color: #000;  text-decoration: none; padding: 14px 20px; margin: 2rem 0; color: #ffffff; display: inline-block; mso-padding-alt: 0;">
			<!--[if mso]>
				<i style="letter-spacing: 25px; mso-font-width: -100%; mso-text-raise: 30pt;">&nbsp;</i>
			<![endif]-->

			<span style="mso-text-raise: 15pt;">' . esc_html( $text ) . '</span>

			<!--[if mso]>
				<i style="letter-spacing: 25px; mso-font-width: -100%;">&nbsp;</i>
			<![endif]-->
		</a>
		';

		return $this;
	}

	/**
	 * Add a list to the email body.
	 *
	 * @param string[] $items The list items to add.
	 *
	 * @return self
	 */
	public function list( array $items ) {
		if ( ! $items ) {
			return $this;
		}

		$this->body .= '<ul>';

		foreach ( $items as $text ) {
			$this->body .= '<li class="margin-bottom: 2rem; margin-top: 1rem;">' . esc_html( $text ) . '</li>';
		}

		$this->body .= '</ul>';

		return $this;
	}

	/**
	 * Send the email.
	 *
	 * @return bool
	 */
	public function send(): bool {
		if ( ! $this->subject ) {
			$this->subject = __( 'Notification', 'courser' );
		}

		$this->subject .= ' | ' . get_bloginfo( 'blogname' );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
		);

		if ( $this->from ) {
			$headers[] = 'From:' . $this->from;
		}

		if ( $this->cc ) {
			foreach ( $this->cc as $recipient ) {
				$headers[] = 'Cc:' . $recipient;
			}
		}

		return wp_mail(
			$this->to,
			$this->subject,
			$this->body,
			$headers
		);
	}
}
